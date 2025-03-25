<?php

namespace App\Http\Data\HumanTalentMgt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Data\HumanTalentMgt\ComunData;
use DateTime;
use Exception;
use PDO;

class ContractData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    // ruta de prueba
    public static function getCarrer()
    {
        $query = DB::table('PLLA_CARRERA')
            ->select('id_carrera', 'nombre')
            ->where('VIGENCIA', 1)->get();
    }

    public static function estado_cont($request)
    {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $tipo = $request->tipo;
        $q = DB::table('eliseo.plla_estado_cont_depto as a')
            ->where('a.id_entidad', $id_entidad)
            ->where('a.id_depto', $id_depto)
            ->where('a.tipo', $tipo)
            ->select('a.id_estado_cont')
            ->pluck('a.id_estado_cont');

        $idEstadoCont = $q;
        // dd($idEstadoCont);

        $query = DB::table('eliseo.plla_estado_cont as a')
            ->where('a.vigencia', 1)
            ->whereNotIn('a.id_estado_cont', $idEstadoCont)
            ->select('a.id_estado_cont', 'a.nombre', 'a.nombrecorto', 'a.vigencia')
            ->orderBy('a.id_estado_cont')
            ->get();
        return $query;
    }
    public static function listEstadoContDepto($request)
    {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $query = DB::table('eliseo.plla_estado_cont_depto as a')
            ->join('eliseo.plla_estado_cont as b', 'a.id_estado_cont', '=', 'b.id_estado_cont')
            ->where('a.id_entidad', $id_entidad)
            ->where('a.id_depto', $id_depto)
            ->select(
                'a.id_estado_cont_depto',
                'a.id_estado_cont',
                'b.nombre as nombre_estado_count',
                'a.vigencia',
                DB::raw("(case when a.tipo = 'C' then 'CONTRATO' when a.tipo = 'R' then 'RENOVACION' when a.tipo = 'T' then 'CESE' else 'OTROS' end) as tipo "),
                'b.NOMBRECORTO as nombre_estado_count_corto',
                'a.id_entidad',
                'a.id_depto'
            )
            ->orderBy(DB::raw("a.tipo, b.id_estado_cont"))
            ->get();
        return $query;
    }
    public static function deleteListEstadoContDepto($id_estado_cont_dept)
    {
        $query = DB::table('eliseo.plla_estado_cont_depto')->where('id_estado_cont_depto', $id_estado_cont_dept)->delete();
        return $query;
    }
    public static function updateEstadoCont($id_estado_cont_dept, $request)
    {
        $vigencia = $request->vigencia;
        $result = DB::table('eliseo.plla_estado_cont_depto')
            ->where('id_estado_cont_depto', $id_estado_cont_dept)
            ->update([
                'vigencia' => $vigencia,
            ]);
        if ($result) {
            $response = [
                'success' => true,
                'message' => 'El registro ha sido desactivado.',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Intentelo nuevamente',
            ];
        }
        return $response;
    }
    public static function addEstadoContDept($request)
    {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $tipo = $request->tipo;
        $detalle = $request->detail;
        $vigencia = 1;
        $orden = 1;

        foreach ($detalle as $datos) {
            $items = (object) $datos;

            $id_estado_cont_dept =  ComunData::correlativo('eliseo.plla_estado_cont_depto', 'id_estado_cont_depto');
            if ($id_estado_cont_dept > 0) {


                $save = DB::table('eliseo.plla_estado_cont_depto')->insert(
                    [
                        'id_estado_cont_depto' =>  $id_estado_cont_dept,
                        'id_entidad' => $id_entidad,
                        'id_depto' => $id_depto,
                        'tipo' => $tipo,
                        'id_estado_cont' => $items->id_estado_cont,
                        'vigencia' => $vigencia,
                        'orden' => $orden,
                    ]
                );
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No se ha generado correlativo',
                ];
            }
        }
        if ($save) {

            $response = [
                'success' => true,
                'message' => 'Se inserto satisfactoriamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede insertar',
            ];
        }

        return $response;
    }

    public static function getPlantillaContrato($request)
    {
        $sql = DB::table('plla_contrato_plantilla')
            ->where("id_contrato_plantilla", '=', $request->id_contrato_plantilla)
            ->select(
                '*'
            )
            ->get()->first();
        return $sql;
    }

    public static function getPlantilla($request)
    {
        $sql = DB::table('plla_contrato_plantilla');
        $sql->where("id_entidad", '=', $request->id_entidad);
        $sql->where("id_depto", '=', $request->id_depto);
        $sql->where("id_tipo_contrato", '=', $request->id_tipo_contrato);
        $vig = $request->vigencia;
        if ($vig == '1' || $vig == '0') {
            $sql->where("vigencia", '=', $vig);
        }
        $sql->select('*');
        $sql->orderByRaw('vigencia desc');
        $data = $sql->get();
        return $data;
    }

    public static function createPlantilla($data, $id_user)
    {
        $response = [];
        $toInsert = json_decode($data, true);
        $id_contrato_plantilla = ComunData::correlativo('plla_contrato_plantilla', 'id_contrato_plantilla');
        $toInsert['id_contrato_plantilla'] = $id_contrato_plantilla;
        $toInsert['id_user_reg'] = $id_user;
        $toInsert['fecha_reg'] = new DateTime();
        $affected = DB::table('plla_contrato_plantilla')->insert($toInsert);
        if ($affected) {
            $response = [
                'success' => true,
                'id_contrato_plantilla' => $id_contrato_plantilla
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede crear',
            ];
        }
        return $response;
    }

    public static function updatePlantilla($id_contrato_plantilla, $data, $id_user)
    {
        $response = [];
        $toUpdate = json_decode($data, true);
        $toUpdate['id_user_mod'] = $id_user;
        $toUpdate['fecha_mod'] = new DateTime();
        $affected = DB::table('plla_contrato_plantilla')
            ->where('id_contrato_plantilla', $id_contrato_plantilla)
            ->update($toUpdate);
        if ($affected) {
            $response = [
                'success' => true,
                'message' => 'Actualizado correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede actualizar',
            ];
        }
        return $response;
    }

    public static function getParametrosByTipo($request)
    {
        $sql = DB::table('PLLA_CONTRATO_PARAMETRO p')
            ->join("PLLA_TIPO_PARAM_PLANTILLA l", "l.id_tipo_param_plantilla", "=", "p.id_tipo_param_plantilla")
            ->where("p.vigencia", "1")
            ->select(
                'p.id_contrato_parametro',
                'p.parametro',
                'p.significado',
                'l.nombre',
                'l.id_tipo_param_plantilla'
            )
            ->get();
        return $sql;
    }


    public static function getParametros()
    {
        $sql = DB::table('PLLA_CONTRATO_PARAMETRO_TEST')
            ->select(
                '*'
            )
            ->orderByRaw('vigencia desc')
            ->get();
        return $sql;
    }

    public static function createParametros($data)
    {
        $response = [];
        $toInsert = json_decode($data, true);
        $id_contrato_parametro = ComunData::correlativo('PLLA_CONTRATO_PARAMETRO_TEST', 'id_contrato_parametro');
        $toInsert['id_contrato_parametro'] = $id_contrato_parametro;
        //$toInsert['id_user_reg'] = $id_user;
        //$toInsert['fecha_reg'] = new DateTime();
        $affected = DB::table('PLLA_CONTRATO_PARAMETRO_TEST')->insert($toInsert);
        if ($affected) {
            $response = [
                'success' => true
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede crear',
            ];
        }
        return $response;
    }

    public static function updateParametros($id_contrato_parametro, $data)
    {
        $response = [];
        $toUpdate = json_decode($data, true);
        //$toUpdate['id_user_mod'] = $id_user;
        //$toUpdate['fecha_mod'] = new DateTime();
        $affected = DB::table('PLLA_CONTRATO_PARAMETRO_TEST')
            ->where('id_contrato_parametro', $id_contrato_parametro)
            ->update($toUpdate);
        if ($affected) {
            $response = [
                'success' => true,
                'message' => 'Actualizado correctamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede actualizar',
            ];
        }
        return $response;
    }


    public static function getGenerated($request, $id_user, $per_page)
    {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_area = $request->id_area;
        // $id_sedearea = $request->id_sedearea;
        $id_estado_cont_depto = $request->id_estado_cont_depto;
        $key = $request->per_key;

        $restringido = 'N';
        $idNivelAcceso = $request->id_acceso_nivel;

        $ids = [];
        if ($request->has('restringido')) {
            $restringido = $request->restringido;
        }
        $objet = DB::table('eliseo.lamb_acceso_nivel')
            ->where('id_acceso_nivel', $idNivelAcceso)
            ->select('id_tipo_nivel_vista')
            ->first();
        if ($restringido == 'S') {
            $ids = ParameterData::areaNivels($idNivelAcceso);
        }

        $sql = DB::table('plla_contrato c');
        $sql->join('moises.persona p', 'p.id_persona', '=', 'c.id_persona');
        $sql->join('moises.persona_natural pn', 'pn.id_persona', '=', 'p.id_persona');
        $sql->join('MOISES.tipo_documento td', 'td.id_tipodocumento', '=', 'pn.id_tipodocumento');
        $sql->join('plla_perfil_puesto pp', 'pp.id_perfil_puesto', '=', 'c.id_perfil_puesto');
        $sql->join('plla_puesto pu', 'pu.id_puesto', '=', 'pp.id_puesto');
        $sql->join('org_sede_area sa', 'sa.id_sedearea', '=', 'c.id_sedearea');
        $sql->join('org_area a', 'a.id_area', '=', 'sa.id_area');
        $sql->join('MOISES.condicion_laboral cl', 'cl.id_condicion_laboral', '=', 'c.id_condicion_laboral');
        $sql->leftJoin('MOISES.situacion_educativa se', 'se.id_situacion_educativo', '=', 'pp.id_situacion_educativo');
        $sql->join('plla_solic_req_candidato rc', 'rc.id_solic_req_candidato', '=', 'c.id_solic_req_candidato');
        $sql->join('plla_estado_cont_depto ed', 'ed.id_estado_cont_depto', '=', 'c.id_estado_cont_depto');
        $sql->join('plla_estado_cont ec', 'ec.id_estado_cont', '=', 'ed.id_estado_cont');
        $sql->where('c.id_depto', '=', $id_depto);
        $sql->where('c.id_entidad', '=', $id_entidad);
        if ($id_area && $id_area != '') {
            $sql->where('a.id_area', '=', $id_area);
        }
        if ($id_estado_cont_depto && $id_estado_cont_depto != '') {
            $sql->where('c.id_estado_cont_depto', '=', $id_estado_cont_depto);
        }

        if ($restringido == 'S') {
            if ($objet->id_tipo_nivel_vista == 4) {
                $sql->whereIn('a.id_area', $ids);
            }
            if ($objet->id_tipo_nivel_vista == 5) {
                $sql->where('c.id_user_reg', $id_user);
            }
        }
        if ($restringido == 'U') {
            $sql->where('c.id_user_reg', $id_user);
        }

        if ($key && $key != '') {
            $txt = "(upper(replace(concat(concat(p.nombre,p.paterno),p.materno),' ','')) like upper(replace('%" . $key . "%',' ','')) or 
            upper(replace(concat(concat(regexp_substr(p.nombre ,'[^ ]+',1,1),p.paterno),p.materno),' ','')) like upper(replace('%" . $key . "%',' ','')) or 
            upper(replace(concat(concat(regexp_substr(p.nombre ,'[^ ]+',1,2),p.paterno),p.materno),' ','')) like upper(replace('%" . $key . "%',' ','')) or 
            (pn.num_documento like '%" . $key . "%'))";
            $sql->whereraw($txt);
        }
        $sql->select(
            'p.paterno',
            'p.materno',
            'p.nombre',
            'td.siglas',
            'pn.num_documento',
            'pu.nombre as puesto',
            'a.nombre as area',
            'cl.nombre as condicion',
            'se.nombre as grado',
            'rc.nota_final',
            DB::raw("TO_CHAR(c.fecha_ini,'DD/MM/YYYY') as fecha_inicio"),
            DB::raw("TO_CHAR(c.fecha_fin,'DD/MM/YYYY') as fecha_fin"),
            'ec.nombre as estado',
            'ec.id_estado_cont',
            'c.id_contrato',
            'c.id_solic_reque',
            'c.id_persona'
        );

        //$data = $sql->get();
        $data = $sql->paginate((int) $per_page);
        return $data;
    }


    public static function createOrderContract($request, $id_user)
    {
        $response = [];
        $status = null;
        $data = $request->data;
        $type = $request->type;
        //aca nombre de los files
        $toInsert = json_decode($data, true);
        $comentario = $toInsert['comentario'];
        unset($toInsert['comentario']);
        DB::beginTransaction();
        $id_contrato = ComunData::correlativo('plla_contrato', 'id_contrato');
        $toInsert['id_contrato'] = $id_contrato;
        $toInsert['id_user_reg'] = $id_user;
        $toInsert['fecha_reg'] = new DateTime();
        $affected = DB::table('plla_contrato')->insert($toInsert);

        if ($affected) {

            $status_worker = null;
            if ($type == "C" || $type == 'T') {
                $status_worker = ContractData::generateContratoTrabajador($id_contrato, $id_user, $type);
                if ($status_worker['success']) {
                    $id_estado_cont_depto = $toInsert['id_estado_cont_depto'];
                    $status = ContractData::changestatus($id_estado_cont_depto, $id_contrato, $id_user, $comentario);
                } else {
                    DB::rollback();
                    $status['success'] = false;
                }
            }

            if ($status_worker['success']) {
                $file_plan_name = $request->file_plan_name;
                if ($file_plan_name && $file_plan_name != "") {
                    $file_plan = $request->file('file_plan');
                    ContractData::updateFilePlan($file_plan_name, $file_plan, $id_contrato);
                }

                $file_menor_name = $request->file_menor_name;
                if ($file_menor_name && $file_menor_name != "") {
                    $file_menor = $request->file('file_menor');
                    ContractData::updateFileMenor($file_menor_name, $file_menor, $id_contrato);
                }
                $file_missionary_name = $request->file_missionary_name;
                if ($file_missionary_name && $file_missionary_name != "") {
                    $file_missionary = $request->file('file_missionary');
                    ContractData::updateFileMissionary($file_missionary_name, $file_missionary, $id_contrato);
                }
                DB::commit();
                $response = [
                    'success' => true,
                    'id_contrato' => $id_contrato,
                    'status' => $status,
                    'status_worker' => $status_worker
                ];
            } else {
                $response = [
                    'success' => false,
                    'id_contrato' => null,
                    'status' => $status,
                    'status_worker' => $status_worker,
                    'message' => $status_worker['msgerror']
                ];

            }

        } else {
            DB::rollback();
            $response = [
                'success' => false,
                'message' => 'No se puede crear',
            ];
        }
        return $response;
    }

    public static function updateOrderContract($request, $id_user)
    {
        $response = [];
        $status = null;
        $data = $request->data;
        $type = $request->type;
        $toUpdate = json_decode($data, true);
        $comentario = $toUpdate['comentario'];
        unset($toUpdate['comentario']);
        DB::beginTransaction();
        $id_contrato = $toUpdate['id_contrato'];
        $toUpdate['id_user_mod'] = $id_user;
        $toUpdate['fecha_mod'] = new DateTime();
        $affected = DB::table('plla_contrato')
            ->where('id_contrato', $id_contrato)->update($toUpdate);
        if ($affected) {
            $status_worker = null;
            if ($type == "C" || $type == 'T') {
                $status_worker = ContractData::generateContratoTrabajador($id_contrato, $id_user, $type);
                if ($status_worker['success']) {
                    $id_estado_cont_depto = $toUpdate['id_estado_cont_depto'];
                    $status = ContractData::changestatus($id_estado_cont_depto, $id_contrato, $id_user, $comentario);
                } else {
                    DB::rollback();
                    $status['success'] = false;
                }
            } else if ($type == "A") {
                $status_worker['success'] = true;
            }
            if ($status_worker['success']) {
                $file_plan_name = $request->file_plan_name;
                if ($file_plan_name && $file_plan_name != "") {
                    $file_plan = $request->file('file_plan');
                    ContractData::updateFilePlan($file_plan_name, $file_plan, $id_contrato);
                }
                $file_menor_name = $request->file_menor_name;
                if ($file_menor_name && $file_menor_name != "") {
                    $file_menor = $request->file('file_menor');
                    ContractData::updateFileMenor($file_menor_name, $file_menor, $id_contrato);
                }

                $file_contrato_name = $request->file_contrato_name;
                if ($file_contrato_name && $file_contrato_name != "") {
                    $file_contrato = $request->file('file_contrato');
                    ContractData::updateFileContrato($file_contrato_name, $file_contrato, $id_contrato);
                }
                $file_tregistro_name = $request->file_tregistro_name;
                if ($file_tregistro_name && $file_tregistro_name != "") {
                    $file_tregistro = $request->file('file_tregistro');
                    ContractData::updateFileTRegistro($file_tregistro_name, $file_tregistro, $id_contrato);
                }
                $file_missionary_name = $request->file_missionary_name;
                if ($file_missionary_name && $file_missionary_name != "") {
                    $file_missionary = $request->file('file_missionary');
                    ContractData::updateFileMissionary($file_missionary_name, $file_missionary, $id_contrato);
                }
                DB::commit();
                $response = [
                    'success' => true,
                    'status' => $status,
                    'status_worker' => $status_worker
                ];
            } else {
                $response = [
                    'success' => false,
                    'status' => $status,
                    'status_worker' => $status_worker,
                    'message' => $status_worker['msgerror']
                ];
            }
        } else {
            DB::rollback();
            $response = [
                'success' => false,
                'message' => 'No se puede actualizar'
            ];
        }
        return $response;
    }

    public static function generateContratoTrabajador($id_contrato, $id_user, $opc)
    {
        //PKG_HUMAN_TALENT_MGT.SP_GEN_CONTRATO_TRABAJADOR(P_ID_CONTRATO NUMBER,P_ID_USER NUMBER,P_OPC VARCHAR2,P_ERROR OUT number,P_MSGERROR out varchar2)
        $response = [];
        try {
            $nerror = 0;
            $msgerror = '';
            for ($i = 1; $i <= 200; $i++) {
                $msgerror .= '0';
            }
            DB::beginTransaction();
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_HUMAN_TALENT_MGT.SP_GEN_CONTRATO_TRABAJADOR(
                            :P_ID_CONTRATO,
                            :P_ID_USER,
                            :P_OPC,
                            :P_ERROR,
                            :P_MSGERROR
                            ); end;");
            $stmt->bindParam(':P_ID_CONTRATO', $id_contrato, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_USER', $id_user, PDO::PARAM_INT);
            $stmt->bindParam(':P_OPC', $opc);
            $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msgerror);
            $stmt->execute();
            DB::commit();

            $response = [
                'error' => $nerror,
                'msgerror' => $msgerror,
                'success' => true
            ];
        } catch (Exception $e) {
            DB::rollback();
            $response = [
                'error' => $e,
                'msgerror' => $e->getMessage(),
                'success' => false
            ];
        }
        return $response;
    }
    public static function updateFilePlan($file_plan_name, $file_plan, $id_contrato)
    {
        $destino = 'gth';
        if (file_exists($destino . '/' . $file_plan_name)) {
            unlink($destino . '/' . $file_plan_name);
        }
        $file_plan->move($destino, $file_plan_name);
        return DB::table('plla_contrato')
            ->where('id_contrato', $id_contrato)
            ->update(
                ['plancap_url' =>   $file_plan_name]
            );
    }

    public static function updateFileMenor($file_menor_name, $file_menor, $id_contrato)
    {
        $destino = 'gth';
        if (file_exists($destino . '/' . $file_menor_name)) {
            unlink($destino . '/' . $file_menor_name);
        }
        $file_menor->move($destino, $file_menor_name);
        return DB::table('plla_contrato')
            ->where('id_contrato', $id_contrato)
            ->update(
                ['menoredad_url' =>   $file_menor_name]
            );
    }

    public static function updateFileContrato($file_contrato_name, $file_contrato, $id_contrato)
    {
        $destino = 'gth';
        if (file_exists($destino . '/' . $file_contrato_name)) {
            unlink($destino . '/' . $file_contrato_name);
        }
        $file_contrato->move($destino, $file_contrato_name);
        return DB::table('plla_contrato')
            ->where('id_contrato', $id_contrato)
            ->update(
                ['CONTARTO_URL' =>   $file_contrato_name]
            );
    }

    public static function updateFileTRegistro($file_tregistro_name, $file_tregistro, $id_contrato)
    {
        $destino = 'gth';
        if (file_exists($destino . '/' . $file_tregistro_name)) {
            unlink($destino . '/' . $file_tregistro_name);
        }
        $file_tregistro->move($destino, $file_tregistro_name);
        return DB::table('plla_contrato')
            ->where('id_contrato', $id_contrato)
            ->update(
                ['TREGISTRO_URL' =>   $file_tregistro_name]
            );
    }

    public static function updateFileMissionary($file_missionary_name, $file_missionary, $id_contrato)
    {
        $destino = 'gth';
        if (file_exists($destino . '/' . $file_missionary_name)) {
            unlink($destino . '/' . $file_missionary_name);
        }
        $file_missionary->move($destino, $file_missionary_name);
        return DB::table('plla_contrato')
            ->where('id_contrato', $id_contrato)
            ->update(
                ['MISIONERO_URL' =>   $file_missionary_name]
            );
    }

    public static function changestatus($id_estado_cont_depto, $id_contrato, $id_autoriza, $comentario)
    {
        try {
            $response = [];
            $id_contrato_estado = ComunData::correlativo('plla_contrato_estado', 'id_contrato_estado');
            DB::beginTransaction();
            DB::table('plla_contrato_estado')->insert(
                [
                    'id_contrato_estado' => $id_contrato_estado,
                    'id_estado_cont_depto' => $id_estado_cont_depto,
                    'id_contrato' => $id_contrato,
                    'comentario' => $comentario,
                    'id_autoriza' => $id_autoriza,
                    'fecha' => new DateTime()
                ]
            );
            DB::commit();
            $response = [
                'success' => true,
                'message' => 'inserted successfully'
            ];
        } catch (Exception $e) {
            DB::rollback();
            $response = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
        return $response;
    }

    public static function getContractToGen($request)
    {
        $estado = $request->id_estado_cont;
        $query = DB::table('eliseo.plla_contrato c');
        $query->join('PLLA_ESTADO_CONT_DEPTO b', 'c.id_estado_cont_depto', '=', 'b.id_estado_cont_depto');
        $query->join('PLLA_ESTADO_CONT e', 'b.ID_ESTADO_CONT', '=', 'e.ID_ESTADO_CONT');
        $query->join('moises.persona p', 'p.id_persona', '=', 'c.id_persona');
        $query->join('moises.persona_natural pn', 'pn.id_persona', '=', 'p.id_persona');
        $query->join('moises.tipo_documento td', 'td.id_tipodocumento', '=', 'pn.id_tipodocumento');
        $query->join('org_sede_area sa', 'sa.id_sedearea', '=', 'c.id_sedearea');
        $query->join('org_area a', 'a.id_area', '=', 'sa.id_area');
        $query->join('conta_entidad_depto cc', 'cc.id_depto', '=', 'sa.id_depto');
        $query->join('plla_perfil_puesto pp', 'pp.id_perfil_puesto', '=', 'c.id_perfil_puesto');
        $query->join('plla_puesto pu', 'pu.id_puesto', '=', 'pp.id_puesto');
        $query->leftJoin('moises.situacion_educativa se', 'se.id_situacion_educativo', '=', 'pn.id_situacion_educativo');
        $query->leftJoin('moises.tipo_ocupacion oc', 'oc.id_tipo_ocupacion', '=', 'c.id_tipo_ocupacion');
        $query->join('moises.condicion_laboral cl', 'cl.id_condicion_laboral', '=', 'c.id_condicion_laboral');
        $query->join('moises.tipo_tiempo_trabajo tt', 'tt.id_tipo_tiempo_trabajo', '=', 'c.id_tipo_tiempo_trabajo');
        $query->where('c.id_contrato', '=', $request->id_contrato);
        $query->select(
            'p.nombre',
            'p.paterno',
            'p.materno',
            'p.id_persona',
            'td.siglas',
            'pn.num_documento',
            'pn.fec_nacimiento',
            'a.nombre as area',
            'cc.nombre as ccosto',
            'pu.nombre as puesto',
            'se.nombre as grado',
            'cl.nombre as condicion',
            'tt.nombre as jornada',
            'c.sueldo',
            'c.fecha_ini',
            'c.fecha_fin',
            'c.id_ccosto',
            'cc.id_depto as id_centro',
            'e.nombre as estado',
            'e.id_estado_cont',
            'oc.nombre as ocupacion',
            DB::raw("FC_MGT_EST_APROB_CONTRATO(c.id_entidad,c.id_depto,'C',b.ID_ESTADO_CONT,'" . $estado . "') as aprobar"),
            'c.*',
            DB::raw("'" . url('') . "/gth/'||c.cv_url AS url"),
            DB::raw("'" . url('') . "/gth/'||c.menoredad_url AS menor_file"),
            DB::raw("'" . url('') . "/gth/'||c.plancap_url AS plan_cap_file"),
            DB::raw("'" . url('') . "/gth/'||c.tregistro_url AS tregistro_file"),
            DB::raw("'" . url('') . "/gth/'||c.contarto_url AS contrato_file"),
            DB::raw("'" . url('') . "/gth/'||c.misionero_url AS misionero_file"),
            DB::raw("'" . url('') . "/gth/'||c.contarto_url AS contrato_file")
        );
        $data = $query->get()->first();
        if ($data) {
            $escala = RequestData::getScaleSalary($data->id_perfil_puesto, $data->id_entidad, $data->id_depto);
            $data->escala = $escala;
            $fmr = RequestData::getFMR($data->id_entidad);
            $data->fmr = $fmr;
        }

        return $data;
    }

    public static function getContract($request, $id_user, $per_page)
    {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_area = $request->id_area;
        $id_sedearea = $request->id_sedearea;
        $id_estado_cont_depto = $request->id_estado_cont_depto;
        $key = $request->per_key;
        $estado = $request->id_estado_cont;
        $estado_acceso = $request->estado_acceso;

        $restringido = 'N';
        $idNivelAcceso = $request->id_acceso_nivel;

        $ids = [];
        if ($request->has('restringido')) {
            $restringido = $request->restringido;
        }
        $objet = DB::table('eliseo.lamb_acceso_nivel')
            ->where('id_acceso_nivel', $idNivelAcceso)
            ->select('id_tipo_nivel_vista')
            ->first();
        if ($restringido == 'S') {
            $ids = ParameterData::areaNivels($idNivelAcceso);
        }


        $sql = DB::table('plla_contrato c');
        $sql->join('PLLA_ESTADO_CONT_DEPTO b', 'c.id_estado_cont_depto', '=', 'b.id_estado_cont_depto');
        $sql->join('PLLA_ESTADO_CONT e', 'b.ID_ESTADO_CONT', '=', 'e.ID_ESTADO_CONT');
        $sql->join('moises.persona p', 'p.id_persona', '=', 'c.id_persona');
        $sql->join('moises.persona_natural pn', 'pn.id_persona', '=', 'p.id_persona');
        $sql->join('MOISES.tipo_documento td', 'td.id_tipodocumento', '=', 'pn.id_tipodocumento');
        $sql->join('plla_perfil_puesto pp', 'pp.id_perfil_puesto', '=', 'c.id_perfil_puesto');
        $sql->join('plla_puesto pu', 'pu.id_puesto', '=', 'pp.id_puesto');
        $sql->join('org_sede_area sa', 'sa.id_sedearea', '=', 'c.id_sedearea');
        $sql->join('org_area a', 'a.id_area', '=', 'sa.id_area');
        $sql->join('MOISES.condicion_laboral cl', 'cl.id_condicion_laboral', '=', 'c.id_condicion_laboral');
        $sql->leftJoin('MOISES.situacion_educativa se', 'se.id_situacion_educativo', '=', 'pn.id_situacion_educativo');
        $sql->join('plla_solic_req_candidato rc', 'rc.id_solic_req_candidato', '=', 'c.id_solic_req_candidato');
        //$sql->join('plla_estado_cont_depto ed', 'ed.id_estado_cont_depto', '=', 'c.id_estado_cont_depto');
        //$sql->join('plla_estado_cont ec', 'ec.id_estado_cont', '=', 'ed.id_estado_cont');
        $sql->join('moises.tipo_tiempo_trabajo tt', 'tt.id_tipo_tiempo_trabajo', '=', 'c.id_tipo_tiempo_trabajo');
        $sql->where('c.id_depto', '=', $id_depto);
        $sql->where('c.id_entidad', '=', $id_entidad);
        if ($id_area && $id_area != '') {
            $sql->where('a.id_area', '=', $id_area);
        }
        if ($id_estado_cont_depto && $id_estado_cont_depto != '') {
            $sql->where('c.id_estado_cont_depto', '=', $id_estado_cont_depto);
        }
        if ($key && $key != '') {
            $txt = "(upper(replace(concat(concat(p.nombre,p.paterno),p.materno),' ','')) like upper(replace('%" . $key . "%',' ','')) or 
            upper(replace(concat(concat(regexp_substr(p.nombre ,'[^ ]+',1,1),p.paterno),p.materno),' ','')) like upper(replace('%" . $key . "%',' ','')) or 
            upper(replace(concat(concat(regexp_substr(p.nombre ,'[^ ]+',1,2),p.paterno),p.materno),' ','')) like upper(replace('%" . $key . "%',' ','')) or 
            (pn.num_documento like '%" . $key . "%'))";
            $sql->whereraw($txt);
        }
        $sql->whereraw("b.ID_ESTADO_CONT not in('00','09')");
        $sql->where('e.ID_ESTADO_CONT', '=', $estado);
        if ($restringido == 'S') {
            if ($objet->id_tipo_nivel_vista == 4) {
                $sql->whereIn('a.id_area', $ids);
            }
            if ($objet->id_tipo_nivel_vista == 5) {
                $sql->where('c.id_user_reg', $id_user);
            }
        }
        if ($restringido == 'U') {
            $sql->where('c.id_user_reg', $id_user);
        }
        $sql->select(
            'p.paterno',
            'p.materno',
            'p.nombre',
            'td.siglas',
            'pn.num_documento',
            'pu.nombre as puesto',
            'a.nombre as area',
            'cl.nombre as condicion',
            'se.nombre as grado',
            'rc.nota_final',
            DB::raw("TO_CHAR(c.fecha_ini,'DD/MM/YYYY') as fecha_inicio"),
            DB::raw("TO_CHAR(c.fecha_fin,'DD/MM/YYYY') as fecha_fin"),
            DB::raw("FC_MGT_EST_APROB_CONTRATO(c.id_entidad,c.id_depto,'C',b.ID_ESTADO_CONT,'" . $estado_acceso . "') as aprobar"),
            'e.nombre as estado',
            'e.id_estado_cont',
            'c.id_contrato',
            'c.id_solic_reque',
            'c.id_persona'
        );
        $data = $sql->paginate((int) $per_page);
        return $data;
    }

    public static function getContractActiveWorker($request, $per_page)
    {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_sedearea = $request->id_sedearea;
        $id_estado_cont_depto = $request->id_estado_cont_depto;
        $key = $request->per_key;
        $estado = $request->id_estado_cont;
        $estado_acceso = $request->estado_acceso;
        $sql = DB::table('plla_contrato c');
        $sql->join('PLLA_ESTADO_CONT_DEPTO b', 'c.id_estado_cont_depto', '=', 'b.id_estado_cont_depto');
        $sql->join('PLLA_ESTADO_CONT e', 'b.ID_ESTADO_CONT', '=', 'e.ID_ESTADO_CONT');
        $sql->join('moises.persona p', 'p.id_persona', '=', 'c.id_persona');
        $sql->join('moises.persona_natural pn', 'pn.id_persona', '=', 'p.id_persona');
        $sql->join('MOISES.tipo_documento td', 'td.id_tipodocumento', '=', 'pn.id_tipodocumento');
        $sql->join('plla_perfil_puesto pp', 'pp.id_perfil_puesto', '=', 'c.id_perfil_puesto');
        $sql->join('plla_puesto pu', 'pu.id_puesto', '=', 'pp.id_puesto');
        $sql->join('org_sede_area sa', 'sa.id_sedearea', '=', 'c.id_sedearea');
        $sql->join('org_area a', 'a.id_area', '=', 'sa.id_area');
        $sql->join('MOISES.condicion_laboral cl', 'cl.id_condicion_laboral', '=', 'c.id_condicion_laboral');
        $sql->leftJoin('MOISES.situacion_educativa se', 'se.id_situacion_educativo', '=', 'pn.id_situacion_educativo');
        $sql->join('moises.tipo_tiempo_trabajo tt', 'tt.id_tipo_tiempo_trabajo', '=', 'c.id_tipo_tiempo_trabajo');
        $sql->where('c.id_depto', '=', $id_depto);
        $sql->where('c.id_entidad', '=', $id_entidad);

        if ($id_sedearea && $id_sedearea != '') {
            $sql->where('c.id_sedearea', '=', $id_sedearea);
        }
        if ($id_estado_cont_depto && $id_estado_cont_depto != '') {
            $sql->where('c.id_estado_cont_depto', '=', $id_estado_cont_depto);
        }
        if ($key && $key != '') {
            $txt = "(upper(replace(concat(concat(p.nombre,p.paterno),p.materno),' ','')) like upper(replace('%" . $key . "%',' ','')) or 
            upper(replace(concat(concat(regexp_substr(p.nombre ,'[^ ]+',1,1),p.paterno),p.materno),' ','')) like upper(replace('%" . $key . "%',' ','')) or 
            upper(replace(concat(concat(regexp_substr(p.nombre ,'[^ ]+',1,2),p.paterno),p.materno),' ','')) like upper(replace('%" . $key . "%',' ','')) or 
            (pn.num_documento like '%" . $key . "%'))";
            $sql->whereraw($txt);
        }
        $sql->whereraw("b.ID_ESTADO_CONT not in('00','09')");
        $sql->whereraw("c.id_solic_reque is null");
        $sql->where('e.ID_ESTADO_CONT', '=', $estado);
        $sql->select(
            'p.paterno',
            'p.materno',
            'p.nombre',
            'td.siglas',
            'pn.num_documento',
            'pu.nombre as puesto',
            'a.nombre as area',
            'cl.nombre as condicion',
            'se.nombre as grado',
            // 'rc.nota_final',
            DB::raw("TO_CHAR(c.fecha_ini,'DD/MM/YYYY') as fecha_inicio"),
            DB::raw("TO_CHAR(c.fecha_fin,'DD/MM/YYYY') as fecha_fin"),
            DB::raw("FC_MGT_EST_APROB_CONTRATO(c.id_entidad,c.id_depto,'C',b.ID_ESTADO_CONT,'" . $estado_acceso . "') as aprobar"),
            'e.nombre as estado',
            'e.id_estado_cont',
            'c.id_contrato',
            'c.id_solic_reque',
            'c.id_persona'
        );
        $data = $sql->paginate((int) $per_page);
        return $data;
    }
    public static function getContractActive($id_contrato)
    {
        $sql = DB::table('plla_contrato c');
        $sql->join('org_sede_area sa', 'sa.id_sedearea', '=', 'c.id_sedearea');
        $sql->join('org_area a', 'a.id_area', '=', 'sa.id_area');
        $sql->join('moises.persona p', 'p.id_persona', '=', 'c.id_persona');
        $sql->join('moises.persona_natural pn', 'pn.id_persona', '=', 'p.id_persona');
        $sql->join('MOISES.tipo_documento td', 'td.id_tipodocumento', '=', 'pn.id_tipodocumento');
        $sql->leftJoin('moises.tipo_ocupacion oc', 'oc.id_tipo_ocupacion', '=', 'c.id_tipo_ocupacion');
        $sql->leftJoin('MOISES.situacion_educativa se', 'se.id_situacion_educativo', '=', 'pn.id_situacion_educativo');
        $sql->where('c.id_contrato', '=', $id_contrato);
        $sql->select(
            'c.*',
            'a.nombre',
            DB::raw("p.id_persona, (p.nombre ||' '|| p.paterno ||' '|| p.materno) as persona"),
            'pn.num_documento',
            //'pn.siglas',
            'pn.fec_nacimiento',
            'td.siglas',
            'oc.nombre as ocupacion',
            'pn.id_situacion_educativo',
            'se.nombre as situacion_educativo',
            DB::raw("'" . url('') . "/gth/'||c.cv_url AS url"),
            DB::raw("'" . url('') . "/gth/'||c.menoredad_url AS menor_file"),
            DB::raw("'" . url('') . "/gth/'||c.plancap_url AS plan_cap_file"),
            DB::raw("'" . url('') . "/gth/'||c.tregistro_url AS tregistro_file"),
            DB::raw("'" . url('') . "/gth/'||c.contarto_url AS contrato_file")
        );
        $data = $sql->get()->first();
        if ($data) {
            $data->escala = RequestData::getScaleSalary($data->id_perfil_puesto, $data->id_entidad, $data->id_depto);
        }
        return $data;
    }


    public static function generateTxt($request)
    {
        $all_selected = $request->all_selected;
        $list_checked = json_decode($request->list_checked, true); //$request->list_checked;
        $list_unchecked = json_decode($request->list_unchecked, true); //$request->list_unchecked;
        $data = [];
        if ($all_selected == 1) {
            $list_checked = ContractData::getAllIds($request, $list_unchecked);
            if (count($list_checked) > 0) {
                $data = ContractData::extractData($list_checked);
            }
        } else {
            if (count($list_checked) > 0) {
                $data = ContractData::extractData($list_checked);
            }
        }
        return $data;
    }

    public static function getAllIds($request, $list_unchecked)
    {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_sedearea = $request->id_sedearea;
        $id_estado_cont_depto = $request->id_estado_cont_depto;
        $key = $request->per_key;
        $estado = $request->id_estado_cont;
        $sql = DB::table('plla_contrato c');
        $sql->join('PLLA_ESTADO_CONT_DEPTO b', 'c.id_estado_cont_depto', '=', 'b.id_estado_cont_depto');
        $sql->join('PLLA_ESTADO_CONT e', 'b.ID_ESTADO_CONT', '=', 'e.ID_ESTADO_CONT');
        $sql->join('moises.persona p', 'p.id_persona', '=', 'c.id_persona');
        $sql->join('moises.persona_natural pn', 'pn.id_persona', '=', 'p.id_persona');
        //$sql->join('MOISES.tipo_documento td', 'td.id_tipodocumento', '=', 'pn.id_tipodocumento');
        //$sql->join('plla_perfil_puesto pp', 'pp.id_perfil_puesto', '=', 'c.id_perfil_puesto');
        //$sql->join('plla_puesto pu', 'pu.id_puesto', '=', 'pp.id_puesto');
        //$sql->join('org_sede_area sa', 'sa.id_sedearea', '=', 'c.id_sedearea');
        //$sql->join('org_area a', 'a.id_area', '=', 'sa.id_area');
        //$sql->join('MOISES.condicion_laboral cl', 'cl.id_condicion_laboral', '=', 'c.id_condicion_laboral');
        //$sql->leftJoin('MOISES.situacion_educativa se', 'se.id_situacion_educativo', '=', 'pn.id_situacion_educativo');
        //$sql->join('plla_solic_req_candidato rc', 'rc.id_solic_req_candidato', '=', 'c.id_solic_req_candidato');
        //$sql->join('moises.tipo_tiempo_trabajo tt', 'tt.id_tipo_tiempo_trabajo', '=', 'c.id_tipo_tiempo_trabajo');
        $sql->where('c.id_depto', '=', $id_depto);
        $sql->where('c.id_entidad', '=', $id_entidad);
        if ($id_sedearea && $id_sedearea != '') {
            $sql->where('c.id_sedearea', '=', $id_sedearea);
        }
        if ($id_estado_cont_depto && $id_estado_cont_depto != '') {
            $sql->where('c.id_estado_cont_depto', '=', $id_estado_cont_depto);
        }
        if ($key && $key != '') {
            $txt = "(upper(replace(concat(concat(p.nombre,p.paterno),p.materno),' ','')) like upper(replace('%" . $key . "%',' ','')) or 
            upper(replace(concat(concat(regexp_substr(p.nombre ,'[^ ]+',1,1),p.paterno),p.materno),' ','')) like upper(replace('%" . $key . "%',' ','')) or 
            upper(replace(concat(concat(regexp_substr(p.nombre ,'[^ ]+',1,2),p.paterno),p.materno),' ','')) like upper(replace('%" . $key . "%',' ','')) or 
            (pn.num_documento like '%" . $key . "%'))";
            $sql->whereraw($txt);
        }
        $sql->whereraw("b.ID_ESTADO_CONT not in('00','09')");
        if (count($list_unchecked) > 0) {
            $cadena = '';
            for ($i = 0; $i < count($list_unchecked); $i++) {
                $element = $list_unchecked[$i];
                if ($i > 0) {
                    $cadena = $cadena . ",";
                }
                $cadena = $cadena . "'" . $element . "'";
            }
            $sql->whereraw("c.id_contrato not in(" . $cadena . ")"); //aqui
        }
        $sql->where('e.ID_ESTADO_CONT', '=', $estado);
        $sql->select(
            'c.id_contrato'
        );
        $datos = $sql->get();
        $data = ContractData::getIds($datos);
        return $data;
    }

    public static function getIds($data)
    {
        $ids = [];
        if (count($data) > 0) {
            for ($i = 0; $i < count($data); $i++) {
                array_push($ids, $data[$i]->id_contrato);
            }
        }
        return $ids;
    }

    public static function extractData($lista_ids)
    {
        $data = [];
        $lista_dp = [];
        $lista_dt = [];
        $lista_es = [];
        $lista_fina = [];
        $lista_lufo = [];
        $lista_esta = [];
        $lista_pf = [];
        $lista_pe = [];
        for ($i = 0; $i < count($lista_ids); $i++) {
            $id_contrato = $lista_ids[$i];
            //print_r($id_persona);
            $a = ContractData::getDatosPersonales($id_contrato); //4
            if ($a) {
                array_push($lista_dp, $a);
            }

            $b = ContractData::getDatosTrabajador($id_contrato); //5
            if ($b) {
                array_push($lista_dt, $b);
            }

            $c = ContractData::getDatosBank($id_contrato); //29
            if ($c) {
                array_push($lista_fina, $c);
            }

            $d = ContractData::getEstudios($id_contrato); //29
            if ($d) {
                array_push($lista_es, $d);
            }

            $e = ContractData::getLugarFormacionPPP($id_contrato); //23
            if ($e) {
                array_push($lista_lufo, $e);
            }
            $f = ContractData::getEstablecimiento($id_contrato); //17
            if ($f) {
                array_push($lista_esta, $f);
            }
            $g = ContractData::getFormacionPP($id_contrato); //9
            if ($g) {
                array_push($lista_pf, $g);
            }
            $h = ContractData::getPeriodos($id_contrato); //11
            if ($h) {
                array_push($lista_pe, $h);
            }
        }
        $data = [
            "d_personal" => $lista_dp,
            "d_trabajador" => $lista_dt,
            "d_estudios" => $lista_es,
            "d_financiero" => $lista_fina,
            "d_lugar_fo" => $lista_lufo,
            "d_establecimiento" => $lista_esta,
            "d_estudios_pp" => $lista_pf,
            "d_periodos" => $lista_pe
        ];
        return $data;
    }

    public static function getDatosPersonales($id_contrato)
    {
        $sql = DB::table('moises.persona p');
        $sql->join("plla_contrato c", "c.id_persona", '=', 'p.id_persona');
        $sql->join('moises.persona_natural pn', 'pn.id_persona', '=', 'p.id_persona');
        $sql->join('moises.tipo_documento td', 'td.id_tipodocumento', '=', 'pn.id_tipodocumento');
        $sql->join('moises.tipo_pais tp', 'tp.id_tipopais', '=', 'pn.id_tipopais');
        $sql->where('c.id_contrato', '=', $id_contrato);
        $sql->select(
            'p.id_persona',
            'pn.id_tipodocumento',
            'td.nombre as documento',
            'pn.num_documento',
            'pn.fec_nacimiento',
            'p.paterno',
            'p.materno',
            'p.nombre',
            'pn.sexo',
            'tp.cod_sunat',
            'tp.codemision',
            'pn.celular',
            'pn.correo',
            'tp.nombre as pais',
            'c.id_contrato'
        );
        $data = $sql->get()->first();
        if ($data) {
            $direcciones = ContractData::getDirecciones($data->id_persona);
            if (count($direcciones) > 0) {
                $data->direcciones = $direcciones;
            } else {
                $data = null;
            }
        }
        return $data;
    }

    public static function getDatosTrabajador($id_contrato)
    {
        $sql = DB::table('moises.persona_natural_trabajador t');
        $sql->join('plla_contrato c', 'c.id_persona', '=', 't.id_persona');
        $sql->join('moises.persona_natural pn', 'pn.id_persona', '=', 't.id_persona');
        $sql->join('moises.tipo_pais tp', 'tp.id_tipopais', '=', 'pn.id_tipopais');
        $sql->where('c.id_contrato', '=', $id_contrato);
        $sql->select(
            'pn.id_tipodocumento',
            'pn.num_documento',
            'c.id_tipo_regimen_laboral',
            'pn.id_situacion_educativo',
            'c.id_tipo_ocupacion',
            't.id_tipo_discapacidad',
            't.cuspp',
            'c.id_tipo_sctr_pension',
            'c.id_tipo_contrato',
            'c.sujregalt',
            'c.sujjortramax',
            'c.sujjorhornoc',
            'c.essindicalizado',
            'c.id_periodo_remu',
            'c.sueldo',
            'c.id_situacion_trabajador',
            'c.exotacat',
            'c.id_situacion_especial',
            'c.id_tipo_pago',
            'c.id_tipo_categ_ocupa',
            'c.id_tipo_doble_trib',
            'tp.cod_sunat',
            'tp.codemision',
            'c.id_contrato'
        );
        $data = $sql->get()->first();
        return $data;
    }

    public static function getDatosBank($id_contrato)
    {
        $sql = DB::table('moises.persona_cuenta_bancaria cb');
        $sql->join('plla_contrato c', 'c.id_persona', '=', 'cb.id_persona');
        $sql->join('moises.persona_natural pn', 'pn.id_persona', '=', 'cb.id_persona');
        $sql->join('CAJA_ENTIDAD_FINANCIERA ef', 'ef.id_banco', '=', 'cb.id_banco');
        $sql->where('c.id_contrato', '=', $id_contrato);
        $sql->where('cb.id_tipoctabanco', '=', '3');
        $sql->where('cb.activo', '=', '1');
        $sql->select(
            'pn.id_persona',
            'pn.num_documento',
            'ef.codigo',
            'cb.cuenta',
            'ef.nombre'
        );
        $data = $sql->get()->first();
        return $data;
    }

    public static function getLugarFormacionPPP($id_contrato)
    {
        $sql = DB::table('plla_contrato c');
        $sql->join('moises.persona_natural pn', 'pn.id_persona', '=', 'c.id_persona');
        $sql->join('moises.tipo_pais tp', 'tp.id_tipopais', '=', 'pn.id_tipopais');
        $sql->where('c.id_contrato', '=', $id_contrato);
        $sql->whereRaw("(c.id_condicion_laboral='PP' or c.id_condicion_laboral='P')");
        $sql->select(
            "pn.id_persona",
            "pn.num_documento",
            "tp.codemision"
        );
        $data = $sql->get()->first();
        return $data;
    }

    public static function getEstablecimiento($id_contrato)
    {
        $sql = DB::table('plla_contrato c');
        $sql->join('moises.persona_natural pn', 'pn.id_persona', '=', 'c.id_persona');
        $sql->join('moises.tipo_pais tp', 'tp.id_tipopais', '=', 'pn.id_tipopais');
        $sql->where('c.id_contrato', '=', $id_contrato);
        $sql->select(
            "pn.id_persona",
            "pn.num_documento",
            "tp.codemision"
        );
        $data = $sql->get()->first();
        return $data;
    }

    public static function getFormacionPP($id_contrato)
    {
        $sql = DB::table('moises.persona_natural pn');
        $sql->join('plla_contrato c', 'c.id_persona', '=', 'pn.id_persona');
        $sql->join('moises.persona_informacion_academica ia', 'ia.id_situacion_educativa', '=', 'pn.id_situacion_educativo');
        $sql->join('moises.tipo_pais tp', 'tp.id_tipopais', '=', 'ia.id_pais_procedencia');
        $sql->leftJoin('moises.tipo_ocupacion tc', 'tc.id_tipo_ocupacion', '=', 'c.id_tipo_ocupacion');
        $sql->join('moises.persona_natural_trabajador pt', 'pt.id_persona', '=', 'c.id_persona');
        $sql->where('c.id_contrato', '=', $id_contrato);
        $sql->select(
            'pn.id_persona',
            'pn.id_tipodocumento',
            'pn.num_documento',
            'tp.codemision',
            'pn.id_situacion_educativo',
            'tc.id_tipo_ocupacion',
            'tc.nombre as ocupacion',
            'pn.sexo',
            'pt.esdiscapacitado',
            'c.sujjorhornoc'
        );
        $data = $sql->get()->first();
        if ($data) {
            $es_madre = '0';
            if ($data->sexo == '2') {
                $es_madre = ContractData::getChilds($data->id_persona);
            }
            $data->es_madre = $es_madre;
        }
        return $data;
    }

    public static function getChilds($id_persona)
    {
        $es_madre = '0';
        $sql = DB::table('moises.vinculo_familiar vf');
        $sql->join('moises.persona_natural pn', 'pn.id_persona', '=', 'vf.id_registrado');
        $sql->whereRaw("(vf.id_tipo_vinculo_familiar='05' or vf.id_tipo_vinculo_familiar='06')");
        $sql->where('vf.id_persona', '=', $id_persona);
        $sql->select(
            'vf.id_registrado',
            DB::raw("FLOOR(months_between(sysdate, pn.fec_nacimiento) / 12) as edad")
        );
        $data = $sql->get();
        if (count($data) > 0) {
            for ($i = 0; $i < count($data); $i++) {
                if (intval($data[$i]->edad) < 18) {
                    $es_madre = '1';
                }
            }
        } else {
            return $es_madre;
        }
    }

    public static function getEstudios($id_contrato)
    {
        $sql = DB::table('moises.persona_natural pn');
        $sql->join('plla_contrato c', 'c.id_persona', '=', 'pn.id_persona');
        $sql->join('moises.persona_informacion_academica ia', 'ia.id_situacion_educativa', '=', 'pn.id_situacion_educativo');
        $sql->join('moises.tipo_pais tp', 'tp.id_tipopais', '=', 'ia.id_pais_procedencia');
        $sql->leftJoin('moises.instituciones ii', 'ii.id_instituicion', '=', 'ia.id_institucion');
        $sql->leftJoin('moises.carrera_profesional cp', 'cp.id_carrera_profesional', '=', 'ia.id_carrera');
        $sql->whereRaw('(ia.id_situacion_educativa=11 or ia.id_situacion_educativa=13)');
        //$sql->whereRaw("(c.id_condicion_laboral='PP' or c.id_condicion_laboral='P')");
        $sql->where('c.id_contrato', '=', $id_contrato);
        $sql->select(
            'pn.id_persona',
            'pn.id_tipodocumento',
            'pn.num_documento',
            'ia.id_situacion_educativa',
            DB::raw("case when tp.iso_a3 = 'PER' then 1 else 0 end as es_peru"),
            'ii.id_instituicion',
            'cp.id_carrera_profesional',
            DB::raw("extract(year from ia.fecha_egreso) as anho_egreso")
        );
        $data = $sql->get()->first();
        return $data;
    }

    public static function getPeriodos($id_contrato)
    {
        $sql = DB::table('plla_contrato c');
        $sql->join('moises.persona_natural pn', 'pn.id_persona', '=', 'c.id_persona');
        $sql->join('moises.tipo_pais tp', 'tp.id_tipopais', '=', 'pn.id_tipopais');
        $sql->leftJoin('moises.persona_natural_trabajador pt', 'pt.id_persona', '=', 'c.id_persona');
        $sql->where('c.id_contrato', '=', $id_contrato);
        $sql->select(
            'pn.id_persona',
            'pn.id_tipodocumento',
            'pn.num_documento',
            'tp.codemision',
            DB::raw("case when (c.id_condicion_laboral = 'P' or c.id_condicion_laboral='PP') then 5 else 1 end as categoria"),
            'c.id_tipo_categ_ocupa',
            'pt.id_regimen_pensionaria',
            DB::raw("TO_CHAR(c.fecha_ini,'DD/MM/YYYY') as inicio")
        );
        $data = $sql->get()->first();
        return $data;
    }

    public static function getDirecciones($id_persona)
    {
        $data = [];
        $ids = ["4", "5"];
        for ($i = 0; $i < count($ids); $i++) {
            $dir = ContractData::getDireccion($ids[$i], $id_persona);
            if ($dir) {
                array_push($data, $dir);
            }
        }
        return $data;
    }

    public static function getDireccion($id_tipo_direccion, $id_persona)
    {
        $sql = DB::table('moises.persona_direccion pd');
        $sql->join('moises.tipo_zona tz', 'tz.id_tipozona', '=', 'pd.id_tipozona');
        $sql->join('moises.vw_ubigeo ub', 'ub.id_ubigeo', '=', 'pd.id_ubigeo');
        $sql->where('pd.id_persona', '=', $id_persona);
        $sql->where('pd.id_tipodireccion', '=', $id_tipo_direccion);
        $sql->where('pd.es_activo', '=', '1');
        $sql->select(
            'pd.id_persona',
            'pd.id_tipovia',
            'pd.num_via',
            'pd.departamento',
            'pd.interior',
            'pd.manzana',
            'pd.lote',
            'pd.kilometro',
            'pd.blok',
            'pd.etapa',
            'tz.id_tipozona',
            'pd.tipozona',
            'ub.codigo',
            'pd.id_tipodireccion',
            'pd.tipovia',
            'pd.tipozona',
            'pd.referencia'
        );
        $data = $sql->get()->first();
        return $data;
    }

    public static function getDatosTrabajadorExplicit($id_contrato)
    {
        $sql = DB::table('moises.persona_natural_trabajador t');
        $sql->join('plla_contrato c', 'c.id_persona', '=', 't.id_persona');
        $sql->join('moises.persona_natural pn', 'pn.id_persona', '=', 't.id_persona');
        $sql->leftJoin('MOISES.tipo_regimen_laboral rl', 'rl.id_tipo_regimen_laboral', '=', 'c.id_tipo_regimen_laboral');
        $sql->leftJoin('MOISES.situacion_educativa se', 'se.id_situacion_educativo', '=', 'pn.id_situacion_educativo');
        $sql->leftJoin('MOISES.tipo_ocupacion tn', 'tn.id_tipo_ocupacion', '=', 'c.id_tipo_ocupacion');
        $sql->leftJoin('MOISES.tipo_discapacidad tdd', 'tdd.id_tipo_discapacidad', '=', 't.id_tipo_discapacidad');
        $sql->leftJoin('MOISES.tipo_sctr_pension sctr', 'sctr.id_tipo_sctr_pension', '=', 'c.id_tipo_sctr_pension');
        $sql->leftJoin('tipo_contrato tc', 'tc.id_tipocontrato', '=', 'c.id_tipo_contrato');
        $sql->leftJoin('plla_periodo_remu pr', 'pr.id_periodo_remu', '=', 'c.id_periodo_remu');
        $sql->leftJoin('MOISES.situacion_trabajador st', 'st.id_situacion_trabajador', '=', 'c.id_situacion_trabajador');
        $sql->leftJoin('MOISES.situacion_especial ses', 'ses.id_situacion_especial', '=', 'c.id_situacion_especial');
        $sql->leftJoin('tipo_pago tp', 'tp.id_tipopago', '=', 'c.id_tipo_pago');
        $sql->leftJoin('plla_tipo_categ_ocupa tco', 'tco.id_tipo_categ_ocupa', '=', 'c.id_tipo_categ_ocupa');
        $sql->leftJoin('plla_tipo_doble_trib tdt', 'tdt.id_tipo_doble_trib', '=', 'c.id_tipo_doble_trib');
        $sql->where('c.id_contrato', '=', $id_contrato);
        $sql->select(
            'c.id_tipo_regimen_laboral',
            'rl.nombre as regimen_laboral',
            'pn.id_situacion_educativo',
            'se.nombre as situacion_educativa',
            'c.id_tipo_ocupacion',
            'tn.nombre as tipo_ocupacion',
            't.id_tipo_discapacidad',
            'tdd.nombre as discapacidad',
            't.cuspp',
            'c.id_tipo_sctr_pension',
            'sctr.nombre as sctr',
            'c.id_tipo_contrato',
            'tc.nombre as tipo_contrato',
            'c.sujregalt',
            'c.sujjortramax',
            'c.sujjorhornoc',
            'c.essindicalizado',
            'c.id_periodo_remu',
            'pr.nombre as periodo_remu',
            'c.sueldo',
            'c.id_situacion_trabajador',
            'st.nombre as situacion_trabajador',
            'c.exotacat',
            'c.id_situacion_especial',
            'ses.nombre as situacion_especial',
            'c.id_tipo_pago',
            'tp.nombre as tipo_pago',
            'c.id_tipo_categ_ocupa',
            'tco.nombre as categ_ocupa',
            'c.id_tipo_doble_trib',
            'tdt.nombre as tipo_doble_trib'
        );
        $data = $sql->get()->first();
        return $data;
    }

    public static function getDireccionesExplicit($id_persona)
    {
        $data = [];
        $ids = ["4", "5"];
        for ($i = 0; $i < count($ids); $i++) {
            $dir = ContractData::getDireccionExplicit($ids[$i], $id_persona);
            if ($dir) {
                array_push($data, $dir);
            }
        }
        return $data;
    }

    public static function getDireccionExplicit($id_tipo_direccion, $id_persona)
    {
        $sql = DB::table('moises.persona_direccion pd');
        $sql->leftJoin('moises.tipo_zona tz', 'tz.id_tipozona', '=', 'pd.id_tipozona');
        $sql->leftJoin('moises.vw_ubigeo ub', 'ub.id_ubigeo', '=', 'pd.id_ubigeo');
        $sql->leftJoin('moises.tipo_via tv', 'tv.id_tipovia', '=', 'pd.id_tipovia');
        $sql->where('pd.id_persona', '=', $id_persona);
        $sql->where('pd.id_tipodireccion', '=', $id_tipo_direccion);
        $sql->where('pd.es_activo', '=', '1');
        $sql->select(
            'pd.id_persona',
            'pd.id_tipovia',
            'tv.nombre as tipo_via',
            'pd.num_via',
            'pd.departamento',
            'pd.interior',
            'pd.manzana',
            'pd.lote',
            'pd.kilometro',
            'pd.blok',
            'pd.etapa',
            'tz.id_tipozona',
            'pd.tipozona',
            'ub.codigo',
            'pd.id_tipodireccion',
            'pd.tipovia',
            'pd.tipozona',
            'pd.referencia'
        );
        $data = $sql->get()->first();
        return $data;
    }

    public static function getInfoContractExplicit($request)
    {
        $id_contrato = $request->id_contrato;
        return [
            "d_personal" => ContractData::getDatosPersonalesExplicit($id_contrato),
            "d_trabajador" => ContractData::getDatosTrabajadorExplicit($id_contrato),
            "d_formacion" => ContractData::getFormacionPP($id_contrato),
            "d_cuenta" => ContractData::getDatosBank($id_contrato),
            "d_lugar" => ContractData::getLugarFormacionPPP($id_contrato),
            "d_establecimiento" => ContractData::getEstablecimiento($id_contrato),
            "d_periodo" => ContractData::getPeriodos($id_contrato),
            "d_estudio" => ContractData::getEstudios($id_contrato)
        ];
    }

    public static function getDatosPersonalesExplicit($id_contrato)
    {
        $sql = DB::table('moises.persona p');
        $sql->join('plla_contrato c', 'c.id_persona', '=', 'p.id_persona');
        $sql->join('moises.persona_natural pn', 'pn.id_persona', '=', 'p.id_persona');
        $sql->join('moises.tipo_documento td', 'td.id_tipodocumento', '=', 'pn.id_tipodocumento');
        $sql->join('moises.tipo_pais tp', 'tp.id_tipopais', '=', 'pn.id_tipopais');
        $sql->where('c.id_contrato', '=', $id_contrato);
        $sql->select(
            'p.id_persona',
            'pn.id_tipodocumento',
            'td.nombre as documento',
            'pn.num_documento',
            'pn.fec_nacimiento',
            'p.paterno',
            'p.materno',
            'p.nombre',
            'pn.sexo',
            'tp.cod_sunat',
            'pn.celular',
            'pn.correo',
            'tp.nombre as pais'
        );
        $data = $sql->get()->first();
        if ($data) {
            $direcciones = ContractData::getDireccionesExplicit($data->id_persona);
            $data->direcciones = $direcciones;
        }
        return $data;
    }

}
