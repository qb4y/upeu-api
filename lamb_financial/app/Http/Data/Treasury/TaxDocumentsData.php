<?php

namespace App\Http\Data\Treasury;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Storage\StorageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
use Illuminate\Support\Facades\Input;
use App\Http\Data\FinancesStudent\ComunData;

class TaxDocumentsData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function addMyDocuments($request, $id_user, $id_entidad, $id_depto, $fecha_reg, $tc)
    {
        $id_persona     =    $request->id_persona;
        $id_moneda      =    $request->id_moneda;
        $id_anho        =    $request->id_anho;
        $id_mes         =    $request->id_mes;
        $serie          =    $request->serie;
        $numero         =    $request->numero;
        $fecha          =    $request->fecha;
        $importe        =    $request->importe;
        $importe_me     =    $request->importe_me;
        $motivo         =    $request->motivo;
        $tipo           =    $request->tipo;
        $codigo         =    $request->codigo;
        $estado         =    $request->estado;
        $tramite_pago   =    $request->tramite_pago;
        $id_pbancaria   =    $request->id_pbancaria;
        $archivo        =    Input::file('file_archivo');
        $numero_doc     =   '0000000001';
        $count          =   DB::table('eliseo.caja_documento')->whereNotNull('numero_doc')->count();
        if ($count > 0) {
            $num_doc          =   DB::table('eliseo.caja_documento')->max('numero_doc');
            $numero_doc = str_pad(intval($num_doc) + 1, strlen($num_doc), '0', STR_PAD_LEFT);
        }
        // dd($numero_doc);
        // $file_archivo   =    $request->file_archivo;

        if ($id_moneda == 9) {
            $imp = $importe_me * $tc;
        } else {
            $imp = $importe;
        }

        $id_documento = ComunData::correlativo('eliseo.caja_documento', 'id_documento');
        if ($id_documento > 0) {
            $save = DB::table('eliseo.caja_documento')->insert([
                'id_documento'   =>    $id_documento,
                'id_user'        =>    $id_user,
                'id_persona'     =>    $id_persona,
                'id_moneda'      =>    $id_moneda,
                'id_entidad'     =>    $id_entidad,
                'id_depto'       =>    $id_depto,
                'id_anho'        =>    $id_anho,
                'id_mes'         =>    $id_mes,
                'serie'          =>    $serie,
                'numero'         =>    $numero,
                'fecha'          =>    $fecha,
                'importe'        =>    $imp,
                'importe_me'     =>    $importe_me,
                'motivo'         =>    $motivo,
                'tipo'           =>    $tipo,
                'codigo'         =>    $codigo,
                'estado'         =>    $estado,
                'numero_doc'     =>    $numero_doc,
                'tramite_pago'   =>    $tramite_pago,
                'id_pbancaria'   =>    $id_pbancaria,
            ]);
            if ($save) {
                $motivo = ''; // para el proceso
                $proc = TaxDocumentsData::insertProceso($id_documento, $id_user, $codigo, $fecha_reg, $motivo);
                if ($proc['success']) {
                    $resultFile['message'] = 'Sin archivo';
                    if ($archivo) {
                        $resultFile = TaxDocumentsData::saveFileDocument($id_documento, $request, $archivo); // antes de cambiar recordar que se esta usando en varios lugares
                    }
                    $result = [
                        'success' => true,
                        'message' => 'Se creo satisfactoriamente' . ', ' . $resultFile['message'],
                        'data' => $save,
                    ];
                } else {
                    $result = [
                        'success' => false,
                        'message' => $proc['message'],
                        'data' => $save,
                    ];
                }
            } else {
                $result = [
                    'success' => false,
                    'message' => 'No se pudo guardar',
                    'data' => $save,
                ];
            }
        } else {
            $result = [
                'success' => false,
                'message' => 'No se pudo generar correlativo',
                'data' => $id_documento,
            ];
        }
        return $result;
    }
    public static function addAuthorizeRefusedDocuments($request, $id_user, $fecha_reg)
    {
        $codigo           =    $request->codigo;
        $id_documento     =    $request->id_documento;
        $motivo           =    $request->motivo;
        $fecha_pago       =    $request->fecha_pago;

        $save = DB::table('eliseo.caja_documento')->where('id_documento', $id_documento)->update([
            'codigo'         =>    $codigo,
            'fecha_pago'         =>    $fecha_pago,
        ]);
        if ($save) {
            $proc = TaxDocumentsData::insertProceso($id_documento, $id_user, $codigo, $fecha_reg, $motivo);
            if ($proc['success']) {
                $result = [
                    'success' => true,
                    'message' => 'Se autorizo satisfactoriamente',
                    'data' => $save,
                ];
            } else {
                $result = [
                    'success' => false,
                    'message' => $proc['message'],
                    'data' => $save,
                ];
            }
        } else {
            $result = [
                'success' => false,
                'message' => 'No se pudo guardar',
                'data' => $save,
            ];
        }
        return $result;
    }
    public static function insertProceso($id_documento, $id_user, $codigo, $fecha_reg, $motivo = '')
    { /// el proceso se utiliza en distintos lugares, modificar con precaución
        $object = DB::table('eliseo.proceso_documento')->where('codigo', $codigo)->select('id_dproceso')->first();
        $id_docproceso = ComunData::correlativo('eliseo.caja_documento_proceso', 'id_docproceso');
        if ($id_docproceso > 0) {
            $save = DB::table('eliseo.caja_documento_proceso')->insert([
                'id_docproceso' => $id_docproceso,
                'id_documento' => $id_documento,
                'id_dproceso' => $object->id_dproceso,
                'id_user' => $id_user,
                'fecha' => $fecha_reg,
                'motivo' => $motivo,
            ]);
            if ($save) {
                $result = [
                    'success' => true,
                    'message' => 'Se registro satisfactoriamente',
                ];
            } else {
                $result = [
                    'success' => false,
                    'message' => 'No se pudo guardar',
                ];
            }
        } else {
            $result = [
                'success' => false,
                'message' => 'No se pudo generar correlativo',
            ];
        }
        return $result;
    }
    // public static function saveFileDocument($id_documento, $request, $archivo) { //'// antes de cambiar recordar que se esta usando en varios lugares
     
    //     $formato          = $archivo->getClientOriginalExtension();
    //     // $name_file             = $archivo->getClientOriginalName();
    //     $size             = filesize($archivo);
    //     $folder           = $request->carpeta;
    //     $carpeta='';
    //     $fileAdjunto['nerror']=1;
    //     $tipo = $request->tipo;
    //     $estado = "1";
    //     if ($tipo == "1") {
    //         $carpeta = $folder.'otros';
    //     } elseif ($tipo == "2") {
    //         $carpeta = $folder.'planilla';
    //     }
    //     // dd($formato,$name,$size, $carpeta);
    //     $fileAdjunto = ComunData::uploadFile($archivo, $carpeta); 
    //         if ($fileAdjunto['nerror']==0) { 
    //             $url    = 'data_lamb_financial/'.$carpeta.'/'.$fileAdjunto['filename'];
    //             $id_dfile = ComunData::correlativo('eliseo.caja_documento_file', 'id_dfile');
    //             if ($id_dfile>0) {
    //             $save = DB::table('eliseo.caja_documento_file')->insert([
    //                 "id_dfile" => $id_dfile,
    //                 "id_documento" => $id_documento,
    //                 "nombre" => $fileAdjunto['filename'],
    //                 "formato" => $formato,
    //                 "url" => $url,
    //                 "fecha" => DB::raw('sysdate'),
    //                 "tipo" => $tipo,
    //                 "tamanho" => $size,
    //                 "estado" => $estado,
    //             ]);
    //             if($save) {
    //                 $result = [
    //                     'nerror' => 0,
    //                     'message' => 'Creado',
    //                 ];
    //             } else {
    //                 $resp = ComunData::deleteFilesDirectorio($carpeta, $fileAdjunto['filename'], 'E');
    //                 $result = [
    //                     'nerror' => 1,
    //                     'message' => 'Fallo',
    //                 ];
    //             }
    //         } else {
    //             $result=[
    //                 'nerror' => 1,
    //                 'message'=>'No se pudo generar correlativo',
    //             ];
    //         }
    //         } else {
    //             $result = [
    //                 'nerror' => 1,
    //                 'message' => $fileAdjunto['message'],
    //             ];
    //         }
        
    //     return $result;
    // }
    public static function saveFileDocument($id_documento, $request, $archivo)
    { //'// antes de cambiar recordar que se esta usando en varios lugares

        $formato          = $archivo->getClientOriginalExtension();
        $size             = filesize($archivo);
        $folder           = $request->carpeta;
        $carpeta = '';
        $tipo = $request->tipo;
        if ($tipo == "1") {
            $carpeta = $folder . 'otros';
        } elseif ($tipo == "2") {
            $carpeta = $folder . 'planilla';
        }

        $storage = new StorageController(); 
        $fileAdjunto = $storage->postFile($archivo, $carpeta);
        $nombre = explode("/",$fileAdjunto['data'])[4];
        ComunData::insertDataFile($id_documento, null, $nombre, $formato, $fileAdjunto['data'], '2', null, $size, '2');
        return [
            'nerror' => 0,
            'message' => 'Creado',
        ];
    }
    public static function listMyDocument($request, $id_entidad, $id_depto, $id_user)
    {
        if (!empty($request->all) and $request->all == 'S') {
            $id_user = '';
        }
        $q = DB::table('eliseo.caja_documento as a');
        $q->leftjoin('moises.persona as b', 'a.id_persona', '=', 'b.id_persona');
        $q->leftjoin('eliseo.caja_documento_file as c', 'a.id_documento', '=', 'c.id_documento');
        $q->leftjoin('eliseo.caja_pago_gasto as d', 'a.id_pgasto', '=', 'd.id_pgasto');
        $q->leftjoin('eliseo.caja_pago as e', 'd.id_pago', '=', 'e.id_pago');
        $q->where('a.id_entidad', $id_entidad);
        $q->where('a.id_depto', $id_depto);
        if (!empty($request->codigo)) {
            $q->where('a.codigo', $request->codigo);
        }
        if (!empty($id_user)) {
            $q->where('a.id_user', $id_user);
        }
        if (!empty($request->id_anho)) {
            $q->where('a.id_anho', $request->id_anho);
        }
        if (!empty($request->id_mes)) {
            $q->where('a.id_mes', $request->id_mes);
        }
        $q->select(
            'a.id_documento',
            'a.id_user',
            'a.id_persona',
            'a.id_moneda',
            'a.id_entidad',
            'a.id_depto',
            'a.id_anho',
            'a.id_mes',
            'a.serie',
            'a.numero',
            'a.fecha',
            'a.importe',
            'a.importe_me',
            'a.motivo',
            'a.tipo',
            'a.codigo',
            'a.estado',
            'a.numero_doc',
            'a.tramite_pago',
            DB::raw("(case when a.codigo = 'REGDOC' then 33 when a.codigo = 'AUTDOC' then 66 when a.codigo = 'PRODOC' then 100 when a.codigo = 'RECDOC' then 33 else 0 end) proceso"),
            DB::raw("(case when a.codigo = 'REGDOC' then 'primary' when a.codigo = 'AUTDOC' then 'info' when a.codigo = 'PRODOC' then 'success' when a.codigo = 'RECDOC' then 'danger' else 'danger' end) color"),
            DB::raw("(case when a.codigo = 'REGDOC' then 'REGISTRADO' when a.codigo = 'AUTDOC' then 'AUTORIZADO' when a.codigo = 'PRODOC' then 'PROCESADO' when a.codigo = 'RECDOC' then 'RECHAZADO' else 'OTRO' end) prefijo_proceso"),
            DB::raw("(b.paterno|| ' ' ||b.materno|| ' ' ||b.nombre) as nombres"),
            'c.formato',
            'c.tipo as tipo_file',
            'c.nombre',
            'c.url',
            DB::raw("(select (x.paterno|| ' ' ||x.materno|| ' ' ||x.nombre) from moises.persona x where a.id_user=x.id_persona) as user_reg"),
            DB::raw("(select eliseo.fc_username(max(cdp.id_user)) from eliseo.caja_documento_proceso cdp where a.id_documento=cdp.id_documento and cdp.id_dproceso=2) as usuario_autorize"),
            DB::raw("(select max(cdp.fecha) from eliseo.caja_documento_proceso cdp where a.id_documento=cdp.id_documento and cdp.id_dproceso=2) as fecha_autorize"),

            'a.id_pgasto',
            'e.id_pago',
            'e.id_mediopago'
        );
        $q->orderBy('a.id_documento', 'desc');
        $data = $q->get();
        //   ->where('')
        return $data;
    }
    public static function getProcesosDocuments()
    {
        $data = DB::table('eliseo.proceso_documento')->where('estado', '1')->select('*')->get();
        return $data;
    }
    public static function deleteMyDocuments($id_documento)
    {
        $object = DB::table('eliseo.caja_documento_file')->where('id_documento', $id_documento)->select('nombre', 'tipo', 'url')->first();
        $resp = '';
        if (!empty($object)) {
            $deleteFile = DB::table('eliseo.caja_documento_file')->where('id_documento', $id_documento)->delete();
            if ($deleteFile) {
                $storage = new StorageController(); 
                $storage->destroyFile($object -> url);
            }
        }
        
        $proceso = TaxDocumentsData::deleteProceso($id_documento);
        if ($proceso['success']) {
            $delete = DB::table('eliseo.caja_documento')->where('id_documento', $id_documento)->delete();
            if ($delete) {
                $result = [
                    'success' => true,
                    'message' => 'Elimando' . ', ' . $resp . ', ' . $proceso['message'],
                    'data' => $delete,
                ];
            } else {
                $result = [
                    'success' => false,
                    'message' => 'No se pudo eliminar' . ', ' . $resp,
                    'data' => $delete,
                ];
            }
        } else {
            $result = [
                'success' => false,
                'message' => $proceso['message'],
                'data' => '',
            ];
        }
        return $result;
    }
    public static function deleteProceso($id_documento)
    {
        $codigo = 'REGDOC';
        $value = DB::table('eliseo.proceso_documento')->where('codigo', $codigo)->select('id_dproceso')->first();
        if (!empty($value)) {
            $proc = DB::table('eliseo.caja_documento_proceso')->where('id_documento', $id_documento)->where('id_dproceso', $value->id_dproceso)->delete();
            if ($proc) {
                $result = [
                    'success' => true,
                    'message' => 'Se elimino el proceso',
                ];
            } else {
                $result = [
                    'success' => false,
                    'message' => 'No se pudo eliminar',
                ];
            }
        } else {
            $result = [
                'success' => false,
                'message' => 'No se encontro el proceso',
            ];
        }
        return $result;
    }
    public static function addSeats($request)
    { //  metodo usado par crear y duplicar asiento
        $id_documento       = $request->id_documento;
        $id_fondo           = $request->id_fondo;
        $id_depto           = $request->id_depto;
        $id_cuentaaasi      = $request->id_cuentaaasi;
        $id_restriccion     = $request->id_restriccion;
        $id_ctacte          = $request->id_ctacte;
        $importe            = $request->importe;
        $importe_me         = $request->importe_me;
        $dc                 = $request->dc;
        $editable           = $request->editable;
        $agrupa             = $request->agrupa;
        if ($dc === 'C' and $importe > 0) {
            $importe = $importe * (-1);
        }
        if ($dc === 'C' and $importe_me > 0) {
            $importe_me = $importe_me * (-1);
        }
        $id_casiento = ComunData::correlativo('eliseo.caja_documento_asiento', 'id_casiento');
        if ($id_casiento > 0) {
            $asiento = DB::table('eliseo.caja_documento_asiento')->insert([
                'id_casiento'        => $id_casiento,
                'id_documento'       => $id_documento,
                'id_fondo'           => $id_fondo,
                'id_depto'           => $id_depto,
                'id_cuentaaasi'      => $id_cuentaaasi,
                'id_restriccion'     => $id_restriccion,
                'id_ctacte'          => $id_ctacte,
                'importe'            => $importe,
                'importe_me'         => $importe_me,
                'dc'                 => $dc,
                'editable'           => $editable,
                'agrupa'             => $agrupa,
            ]);
            if ($asiento) {
                $result = [
                    'success' => true,
                    'message' => 'Se registro el asiento',
                ];
            } else {
                $result = [
                    'success' => false,
                    'message' => 'No se pudo registrar',
                ];
            }
        } else {
            $result = [
                'success' => false,
                'message' => 'No se genero correlativo',
            ];
        }
        return $result;
    }
    public static function updateSeats($request, $id_casiento)
    { //  metodo usado par crear y duplicar asiento
        $id_fondo           = $request->id_fondo;
        $id_depto           = $request->id_depto;
        $id_cuentaaasi      = $request->id_cuentaaasi;
        $id_restriccion     = $request->id_restriccion;
        $id_ctacte          = $request->id_ctacte;
        $importe            = $request->importe;
        $importe_me         = $request->importe_me;
        $dc                 = $request->dc;
        $editable           = $request->editable;
        $agrupa             = $request->agrupa;
        if ($dc === 'C' and $importe > 0) {
            $importe = $importe * (-1);
        }
        if ($dc === 'C' and $importe_me > 0) {
            $importe_me = $importe_me * (-1);
        }
        $asiento = DB::table('eliseo.caja_documento_asiento')->where('id_casiento', $id_casiento)->update([
            'id_fondo'           => $id_fondo,
            'id_depto'           => $id_depto,
            'id_cuentaaasi'      => $id_cuentaaasi,
            'id_restriccion'     => $id_restriccion,
            'id_ctacte'          => $id_ctacte,
            'importe'            => $importe,
            'importe_me'         => $importe_me,
            'dc'                 => $dc,
            'editable'           => $editable,
            'agrupa'             => $agrupa,
        ]);
        if ($asiento) {
            $result = [
                'success' => true,
                'message' => 'Se modifico el asiento',
            ];
        } else {
            $result = [
                'success' => false,
                'message' => 'No se pudo modificar',
            ];
        }
        return $result;
    }
    public static function listSeats($id_documento)
    {
        $data = DB::table('eliseo.caja_documento_asiento')
            ->where('id_documento', $id_documento)
            ->select(
                'id_casiento',
                'id_documento',
                'id_cuentaaasi',
                'id_restriccion',
                'id_ctacte',
                'id_fondo',
                'id_depto',
                DB::raw("case when dc = 'C' then abs(importe) else importe end as importe"),
                DB::raw("case when dc = 'C' then abs(importe_me) else importe_me end as importe_me"),
                'dc',
                'editable',
                'agrupa'
            )
            ->orderBy('id_casiento', 'asc')
            ->get();
        return $data;
    }
    public static function deleteAsientoDocumeto($id_casiento)
    {
        $asiento = DB::table('eliseo.caja_documento_asiento')->where('id_casiento', $id_casiento)->delete();
        if ($asiento) {
            $result = [
                'success' => true,
                'message' => 'Se elimino el asiento',
            ];
        } else {
            $result = [
                'success' => false,
                'message' => 'No se pudo eliminar',
            ];
        }
        return $result;
    }
    public static function addSeatsTransaction($request)
    {
        $id_documento = $request->id_documento;
        $id_dinamica = $request->id_dinamica;
        $error = 0;
        $msg_error = "";
        for ($x = 1; $x <= 200; $x++) {
            $msg_error .= "0";
        }
        $pdo = DB::getPdo();
        DB::beginTransaction();
        $stmt = $pdo->prepare("begin PKG_CAJA.SP_DOCUMENTO_ASIENTO(
            :P_ID_DOCUMENTO,
            :P_ID_DINAMICA,
            :P_ERROR,
            :P_MSGERROR); end;");
        $stmt->bindParam(':P_ID_DOCUMENTO', $id_documento, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DINAMICA', $id_dinamica, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msg_error, PDO::PARAM_STR);
        $stmt->execute();
        if ($error == 0) {
            DB::commit();
            $result = [
                'success' => true,
                'message' => $msg_error,
            ];
        } else {
            DB::rollback();
            $result = [
                'success' => false,
                'message' => $msg_error,
            ];
        }
        return $result;
    }
    public static function processDocuments($id_documento)
    {
        $data = DB::table('eliseo.caja_documento as a')
            ->leftjoin('eliseo.caja_documento_proceso as b', 'a.id_documento', '=', DB::raw("b.id_documento and a.id_documento=" . $id_documento . ""))
            ->rightjoin('eliseo.proceso_documento as c', 'b.id_dproceso', '=', 'c.id_dproceso')
            ->leftjoin('eliseo.users as e', 'b.id_user', '=', 'e.id')
            ->select(
                'a.id_documento',
                'a.codigo as codigo_paso',
                'b.fecha',
                'b.id_user',
                'b.motivo',
                'c.nombre',
                'c.codigo',
                'b.id_dproceso',
                'e.email',
                'c.estado',
                'b.id_docproceso',
                DB::raw("(CASE
        WHEN B.ID_DPROCESO IS NOT NULL THEN 'S'
        ELSE (
                CASE
                WHEN (SELECT COUNT(1) FROM CAJA_DOCUMENTO_PROCESO X WHERE X.ID_DOCUMENTO = " . $id_documento . ") = 1 THEN 'S'
                WHEN (SELECT COUNT(1) FROM CAJA_DOCUMENTO_PROCESO X WHERE X.ID_DOCUMENTO = " . $id_documento . ") = 2 AND C.CODIGO = 'RECDOC' AND B.ID_DPROCESO IS NULL THEN 'N'
                WHEN (SELECT COUNT(1) FROM CAJA_DOCUMENTO_PROCESO X WHERE X.ID_DOCUMENTO = " . $id_documento . " AND X.ID_DPROCESO = 2) = 1  AND C.CODIGO = 'PRODOC' THEN 'S'
                ELSE 'N'
                END
            )
        END) ver")
            )
            ->orderBy('c.id_dproceso')
            ->get();
        // $data = DB::table('eliseo.proceso_documento as a')
        // ->leftjoin('eliseo.caja_documento_proceso as b', 'a.id_dproceso', '=', DB::raw("b.id_dproceso and b.id_documento=".$id_documento.""))
        // ->leftjoin('eliseo.users as c', 'b.id_user', '=', 'c.id')
        // ->select('a.id_dproceso', 'a.nombre', 'a.codigo', 'a.estado', 'b.id_docproceso', 'b.fecha', 'b.id_user', 'b.motivo', 'b.id_documento', 'c.email')
        // ->orderBy('a.id_dproceso')
        // ->get();
        return $data;
    }
    public static function getYearDocuments()
    {
        $query1 = DB::table(DB::raw('dual'))
            ->select(DB::raw("to_char(sysdate, 'yyyy') as id_anho"));

        $query2 = DB::table(DB::raw('dual'))
            ->select(DB::raw("to_char(case when to_char(sysdate, 'MM')='12' then  ADD_MONTHS(sysdate,4) else sysdate end ,'yyyy') as id_anho"));

        $query = DB::table('eliseo.caja_documento')
            ->select(DB::raw("to_char(id_anho) as id_anho"))
            ->groupBy('id_anho')
            ->union($query1)
            ->union($query2)
            ->orderBy('id_anho', 'desc')
            ->get();
        return $query;
    }
    public static function listPgastoMyDocument($request, $id_entidad, $id_depto, $id_user)
    {
        if (!empty($request->all) and $request->all == 'S') {
            $id_user = '';
        }
        $q = DB::table('eliseo.caja_documento as a');
        $q->leftjoin('moises.persona as b', 'a.id_persona', '=', 'b.id_persona');
        $q->leftjoin('eliseo.caja_documento_file as c', 'a.id_documento', '=', 'c.id_documento');
        $q->where('a.id_entidad', $id_entidad);
        $q->where('a.id_depto', $id_depto);
        $q->whereNull('a.id_pgasto');
        if (!empty($request->codigo)) {
            $q->where('a.codigo', $request->codigo);
        }
        if (!empty($id_user)) {
            $q->where('a.id_user', $id_user);
        }
        $q->select(
            'a.id_documento',
            'a.id_user',
            'a.id_persona',
            'a.id_moneda',
            'a.id_entidad',
            'a.id_depto',
            'a.id_anho',
            'a.serie',
            'a.numero',
            'a.fecha',
            'a.importe',
            'a.importe_me',
            'a.motivo',
            'a.tipo',
            'a.codigo',
            'a.estado',
            'a.numero_doc',
            DB::raw("(b.paterno|| ' ' ||b.materno|| ' ' ||b.nombre) as nombres"),
            'c.formato',
            'c.tipo as tipo_file',
            'c.nombre',
            'c.url'
        );
        $q->orderBy('a.id_documento', 'desc');
        $data = $q->get();
        //   ->where('')
        return $data;
    }
    public static function addGastoMyDocument($request, $fecha_reg, $id_entidad)
    {
        $id_pago = $request->id_pago;
        $numero = $request->numero;
        $fecha = $request->fecha;
        $medio_pago = $request->medio_pago;
        $detalle = $request->details;

        if (empty($fecha)) { //Acceso de cheque o telecredito
            $fecha = $fecha_reg;
        }

        foreach ($detalle as $value) {
            $items = (object)$value;
            $person = DB::table('moises.persona')->where('id_persona', $items->id_persona)->select('nombre', 'paterno')->first();
            $prefix = '';
            if (!empty($person)) {
                $prefix = $person->nombre[0] . '.' . $person->paterno;
            }
            $data = [
                // 'id_solicitud_mat_alum' => $id_solicitud_mat_alum,
                'id_pago' => $id_pago,
                'id_dinamica' => '',
                'id_persona' => $items->id_persona,
                'id_tipoorigen' => '5',
                'id_moneda' => $items->id_moneda,
                'detalle' =>  $id_entidad . '-' . $prefix . '-' . $medio_pago . '-' . $numero . '-' . $fecha . '-' . $items->motivo,
                'importe' => $items->importe,
                'importe_me' => $items->importe_me,
                'fecha' =>  $fecha_reg,
            ];

            $idCajapgasto = DB::transaction(function () use ($data) {
                DB::table('eliseo.caja_pago_gasto')->insert($data);
                return DB::getSequence()->currentValue('SQ_CAJA_PAGO_GASTO_ID');
            });

            $asiento = DB::table('eliseo.caja_documento_asiento')->where('id_documento', '=', $items->id_documento)->select('*')->get();

            foreach ($asiento as $asie) {
                $val = (object)$asie;
                // $id_gasiento = ComunData::correlativo('eliseo.caja_pago_gasto_asiento', 'id_gasiento');
                $delta = [
                    // 'id_gasiento' => $id_gasiento,
                    'id_pgasto' => $idCajapgasto,
                    'id_cuentaaasi' => $val->id_cuentaaasi,
                    'id_restriccion' => $val->id_restriccion,
                    'id_ctacte' => $val->id_ctacte,
                    'id_fondo' => $val->id_fondo,
                    'id_depto' => $val->id_depto,
                    'importe' => $val->importe,
                    'importe_me' => $val->importe_me,
                    'descripcion' => $items->motivo,
                    'editable' => $val->editable,
                    'id_parent' => '',
                    'id_tiporegistro' => 'D',
                    'dc' => $val->dc,
                    'agrupa' => $val->agrupa,
                ];
                # code...
                $cajapgasto = DB::table('eliseo.caja_pago_gasto_asiento')->insert($delta);
            }

            $documento = DB::table('eliseo.caja_documento')->where('id_documento', '=', $items->id_documento)->update(['id_pgasto' => $idCajapgasto]);

            # code...
        }
        $agregados = DB::table('eliseo.caja_pago_gasto')->where('id_pago', $id_pago)->select('*')->get();

        if (count($agregados) >= count($detalle)) {
            $result = [
                'success' => true,
                'message' => 'Proceso completado exitosamente',
                'data' => $agregados,
            ];
        } else {
            $result = [
                'success' => false,
                'message' => 'No se completo el proceso',
                'data' => [],
            ];
        }
        return $result;
    }
    public static function getPaymentDocument($id_pago)
    {
        $data = DB::table('eliseo.caja_pago as a')
            ->leftjoin('eliseo.caja_pago_file as v', 'a.id_pago', '=', 'v.id_pago')
            ->join('eliseo.conta_moneda as b', 'a.id_moneda', '=', 'b.id_moneda')
            ->leftjoin('eliseo.caja_cuenta_bancaria as c', 'a.id_ctabancaria', '=', 'c.id_ctabancaria')
            ->select('a.*', 'b.simbolo', 'c.nombre as cuenta_bancaria', 'c.cuenta_corriente', 'v.nombre as nombre_file', 'v.formato', 'v.tipo as tipo_file', 'v.url')
            ->where('a.id_pago', $id_pago)
            ->first();
        return $data;
    }
    public static function getSeatsPagoDocument($id_pgasto)
    {
        $data = DB::table('eliseo.conta_asiento as a')
            ->join('eliseo.caja_pago_gasto as b', 'a.id_tipoorigen', '=', DB::raw("b.id_tipoorigen and a.id_origen=b.id_pgasto"))
            ->select('a.*', DB::raw("(case when a.importe > 0 then a.importe else 0 end) debito, 
        (case when a.importe < 0 then a.importe else 0 end) as credito"))
            ->where('b.id_pgasto', $id_pgasto)
            ->orderBy('debito', 'desc')
            ->get();
        return $data;
    }
}
