<?php

namespace App\Http\Data\HumanTalentMgt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\HumanTalentMgt\ComunData;
use DateTime;
use Exception;
use PDO;

class RequestData extends Controller
{

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function getSolicitudes($request, $id_user, $anho, $per_page)
    {


        $id_depto = $request->id_depto;
        $id_entidad = $request->id_entidad;
        $id_area = $request->id_area;
        $id_estado_req = $request->id_estado_req;

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

        $sql = DB::table('plla_solic_reque r');
        $sql->join('org_sede_area a', 'a.id_sedearea', '=', 'r.id_sedearea');
        $sql->join('org_area m', 'm.id_area', '=', 'a.id_area');
        $sql->join('plla_perfil_puesto p', 'p.id_perfil_puesto', '=', 'r.id_perfil_puesto');
        $sql->join('plla_puesto pu', 'pu.id_puesto', '=', 'p.id_puesto');
        $sql->join('plla_modalidad_req mo', 'mo.id_modalidad_req', '=', 'r.id_modalidad_req');
        //$sql->join('plla_motivo_req mt', 'mt.id_motivo_req', '=', 'r.id_motivo_req');
        $sql->join('moises.condicion_laboral cl', 'cl.id_condicion_laboral', '=', 'r.id_condicion_laboral');
        $sql->join('plla_estado_req e', 'e.id_estado_req', '=', 'r.id_estado_req');
        $sql->leftjoin('plla_perfil_puesto_anho m', 'm.id_perfil_puesto', '=', DB::raw("p.id_perfil_puesto and m.id_anho = " . $anho));
        $sql->where('r.id_depto', '=', $id_depto);
        if ($id_entidad && $id_entidad != '') {
            $sql->where('r.id_entidad', '=', $id_entidad);
        }
        if ($id_area && $id_area != '') {
            //$sql->where('r.id_sedearea', '=', $id_sedearea);
            $sql->where('a.id_area', '=', $id_area);
        }
        if ($id_estado_req && $id_estado_req != '') {
            $sql->where('r.id_estado_req', '=', $id_estado_req);
        }
        if ($restringido == 'S') {
            if ($objet->id_tipo_nivel_vista == 4) {
                $sql->whereIn('a.id_area', $ids);
            }
            if ($objet->id_tipo_nivel_vista == 5) {
                $sql->where('r.id_user_reg', $id_user);
            }
        }
        if ($restringido == 'U') {
            $sql->where('r.id_user_reg', $id_user);
        }
        $sql->select(
            'r.id_solic_reque',
            'pu.nombre as puesto',
            'm.nombre as area',
            'mo.nombre as modalidad',
            'cl.nombre as condicion',
            DB::raw("TO_CHAR(r.fecha_ini,'DD/MM/YYYY') as fecha_inicio"),
            DB::raw("TO_CHAR(r.fecha_fin,'DD/MM/YYYY') as fecha_fin"),
            'e.nombre as estado',
            'r.cantidad as asignado',
            'p.id_perfil_puesto',
            'e.id_estado_req',
            DB::raw("coalesce(m.cantidad, 0) as previsto")
        );
        $sql->orderBy('e.id_estado_req', 'desc');
        $data = $sql->paginate((int) $per_page);
        for ($i = 0; $i < count($data); $i++) {
            $cantidad = RequestData::getCantidadSolicitud($data[$i]->id_perfil_puesto);
            $data[$i]->ocupado = $cantidad['cantidad'];
        }
        return $data;
    }

    public static function getSolicitud($id_solicitud, $anho, $url)
    {
        $sql = DB::table('plla_solic_reque a');
        $sql->join('plla_perfil_puesto b', 'b.id_perfil_puesto', '=', 'a.id_perfil_puesto');
        $sql->join('plla_perfil_puesto_anho c', 'c.id_perfil_puesto', '=', 'b.id_perfil_puesto');
        $sql->join('org_sede_area d', 'd.id_sedearea', '=', 'a.id_sedearea');
        $sql->join('org_area e', 'e.id_area', '=', 'd.id_area');
        $sql->where("a.id_solic_reque", '=', $id_solicitud);
        $sql->where("c.id_anho", '=', $anho);
        $sql->select(
            'a.*',
            'b.id_situacion_educativo',
            'b.id_tipo_horario',
            'c.cantidad as pre_cant',
            'e.nombre as area',
            'e.id_area'
        );
        $data = $sql->get();
        $datos = array();
        $datos['requerimiento'] = $data;
        $datos['solicitudes'] = RequestData::getSugerenciasBySolicitud($id_solicitud, $url);
        return $datos;
    }

    public static function getSugerenciasBySolicitud($id_solicitud, $url)
    {
        $sql = DB::table('plla_solic_req_candidato c');
        $sql->join('moises.persona_natural pn', 'pn.id_persona', '=', 'c.id_persona');
        $sql->join('moises.persona p', 'p.id_persona', '=', 'pn.id_persona');
        $sql->join('moises.tipo_documento td', 'td.id_tipodocumento', '=', 'pn.id_tipodocumento');
        $sql->join('plla_estado_req_cand e', 'e.id_estado_req_cand', '=', 'c.id_estado_req_cand');
        $sql->where("id_solic_reque", '=', $id_solicitud);
        $sql->select(
            'c.*',
            'p.nombre',
            'p.paterno',
            'p.materno',
            'pn.sexo',
            'pn.num_documento',
            'td.siglas',
            'e.nombre as estado',
            DB::raw("'" . $url . "/gth/'||c.cv_url AS url")
        );
        $sql->orderBy("e.id_estado_req_cand", "desc");
        $data = $sql->get();
        return $data;
    }

    public static function getCantidadSolicitud($id_perfil_puesto)
    {
        $response = [];
        $sql = DB::table('plla_solic_reque');
        $sql->where("id_perfil_puesto", '=', $id_perfil_puesto);
        $sql->where("id_estado_req", '!=', '00');
        $sql->select(
            DB::raw("sum(cantidad) as cantidad")
        );
        $data = $sql->first();

        $sql2 = DB::table('moises.persona_natural_trabajador n');
        $sql2->join('plla_puesto pu', 'pu.id_puesto', '=', 'n.id_puesto');
        $sql2->join('plla_perfil_puesto p', 'p.id_puesto', '=', 'pu.id_puesto');
        $sql2->where("n.id_situacion_trabajador", '!=', '0');
        $sql2->where("p.id_perfil_puesto", '=', $id_perfil_puesto);
        $sql2->select(DB::raw('count(*) as cantidad'));
        $data2 = $sql2->first();

        $c1 = ($data) ? intval($data->cantidad) : 0;
        $c2 = ($data2) ? intval($data2->cantidad) : 0;
        $response['cantidad'] = $c1 + $c2;
        return $response;
    }

    public static function regRequest($data, $id_user)
    {
        try {
            $response = [];
            $toInsert = json_decode($data, true);
            $id_solic_reque = ComunData::correlativo('plla_solic_reque', 'id_solic_reque');
            $toInsert['id_solic_reque'] = $id_solic_reque;
            $toInsert['id_user_reg'] = $id_user;
            $toInsert['fecha_reg'] = new DateTime();
            $affected = DB::table('plla_solic_reque')->insert($toInsert);
            if ($affected) {
                $response = [
                    'success' => true,
                    'id_solic_reque' => $id_solic_reque,
                    'message' => 'Creado'
                ];
                RequestData::changeStatus($id_solic_reque, $toInsert['id_estado_req'], null, $id_user);
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No se puede crear',
                ];
            }
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        return $response;
    }

    public static function updRequest($id_solic_reque, $data, $comentario, $id_user)
    {
        try {
            $response = [];
            $toUpdate = json_decode($data, true);
            $toUpdate['id_user_mod'] = $id_user;
            $toUpdate['fecha_mod'] = new DateTime();
            $affected = DB::table('plla_solic_reque')->where('id_solic_reque', $id_solic_reque)->update($toUpdate);
            if ($toUpdate["id_estado_req"] == "00") {
                RequestData::changeStatus($id_solic_reque, "00", $comentario, $id_user);
            }
            if ($affected) {
                $response = [
                    'success' => true,
                    'id_solic_reque' => $id_solic_reque,
                    'message' => 'Actualizado'
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No se puede actualizar',
                ];
            }
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        return $response;
    }

    public static function changeStatus($id_solic_reque, $id_estado_req, $comentario, $id_user)
    {
        try {
            $response = [];
            $id_solic_reque_est = ComunData::correlativo('plla_solic_reque_est', 'id_solic_reque_est');
            DB::beginTransaction();
            DB::table('plla_solic_reque_est')->insert(
                [
                    'id_solic_reque_est' => $id_solic_reque_est,
                    'id_solic_reque' => $id_solic_reque,
                    'id_estado_req' => $id_estado_req,
                    'comentario' => $comentario,
                    'id_user_reg' => $id_user,
                    'fecha_reg' => new DateTime()
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

    public static function changeStatusCandidato($id_solic_req_candidato, $id_estado_req_cand, $comentario, $id_user)
    {
        try {
            $response = [];
            $plla_solic_req_candidato_es = ComunData::correlativo('plla_solic_req_candidato_est', 'id_solic_req_candidato_es');
            DB::beginTransaction();
            DB::table('plla_solic_req_candidato_est')->insert(
                [
                    'id_solic_req_candidato_es' => $plla_solic_req_candidato_es,
                    'id_solic_req_candidato' => $id_solic_req_candidato,
                    'id_estado_req_cand' => $id_estado_req_cand,
                    'comentario' => $comentario,
                    'id_user_reg' => $id_user,
                    'fecha_reg' => new DateTime()
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

    public static function regSuggestRequest($request, $id_user)
    {
        $response = [];
        $respose_boolean = true;
        try {
            $id_solic_reque = $request->id_solic_reque;
            $id_estado_req_cand = $request->id_estado_req_cand;
            $candidatos = json_decode($request->candidatos);
            foreach ($candidatos as $datos) {
                $item = (object) $datos;
                $id_solic_req_candidato = ComunData::correlativo('plla_solic_req_candidato', 'id_solic_req_candidato');
                $affected = DB::table('plla_solic_req_candidato')->insert(
                    [
                        "id_solic_req_candidato" => $id_solic_req_candidato,
                        'id_solic_reque' => $id_solic_reque,
                        'id_persona' => $item->id_persona,
                        'id_estado_req_cand' => $id_estado_req_cand,
                        'id_user_reg' => $id_user,
                        'fecha_reg' => new DateTime(),
                        "cv_url" => $item->cvname
                    ]
                );
                if (!$affected) {
                    $respose_boolean = false;
                } else {
                    RequestData::changeStatusCandidato($id_solic_req_candidato, $id_estado_req_cand, null, $id_user);
                }
            }

            $cantidad_files = intval($request->cantidad);
            $destino = 'gth';

            for ($i = 0; $i < $cantidad_files; $i++) {
                $m = 'cvname' . $i;
                $cvname = $request->$m;
                $file = $request->file('archivo' . $i);
                if ($cvname != "") {
                    if (file_exists($destino . '/' . $cvname)) {
                        unlink($destino . '/' . $cvname);
                    }
                    $file->move($destino, $cvname);
                }
            }

            if ($respose_boolean) {
                $response = [
                    'success' => true
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No se puede crear',
                ];
            }
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
        return $response;
    }

    public static function updSuggestRequest($id_solic_req_candidato, $data, $comentario, $id_user)
    {
        try {
            $response = [];
            $toUpdate = json_decode($data, true);
            $toUpdate['id_user_mod'] = $id_user;
            $toUpdate['fecha_mod'] = new DateTime();
            $affected = DB::table('plla_solic_req_candidato')->where('id_solic_req_candidato', $id_solic_req_candidato)->update($toUpdate);
            if ($affected) {
                $response = [
                    'success' => true,
                    'id_solic_req_candidato' => $id_solic_req_candidato,
                    'message' => 'Actualizado'
                ];
                if ($toUpdate["id_estado_req_cand"] == "00") {
                    RequestData::changeStatusCandidato($id_solic_req_candidato, "00", $comentario, $id_user);
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No se puede actualizar',
                ];
            }
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        return $response;
    }

    public static function deleteSuggestRequest($id_solic_req_candidato)
    {

        $affect = DB::table('plla_solic_req_candidato')->where('id_solic_req_candidato', $id_solic_req_candidato)->delete();
        if ($affect > 0) {
            $response = [
                'success' => true,
                'message' => ''
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede eliminar',
            ];
        }
        return $response;
    }



    //////


    public static function listApproved($request)
    {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $sql = DB::table('plla_solic_reque r');
        $sql->join('org_sede_area a', 'a.id_sedearea', '=', 'r.id_sedearea');
        $sql->join('org_area m', 'm.id_area', '=', 'a.id_area');
        $sql->join('plla_motivo_req mr', 'mr.id_motivo_req', '=', 'r.id_motivo_req');
        $sql->where('r.id_depto', '=', $id_depto);
        $sql->where('r.id_entidad', '=', $id_entidad);
        $sql->where('r.id_estado_req', '=', '04');
        $sql->select(
            'r.id_solic_reque',
            'm.nombre as area',
            'mr.nombre as motivo',
            DB::raw("TO_CHAR(r.fecha_ini,'DD/MM/YYYY') as fecha_inicio")
        );
        //$sql->orderBy('e.id_estado_req', 'desc');
        $data = $sql->get();
        return $data;
    }

    public static function listStatusContract($request)
    {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $sql = DB::table('plla_estado_cont_depto dp');
        $sql->join('plla_estado_cont c', 'dp.id_estado_cont', '=', 'c.id_estado_cont');
        $sql->where('dp.id_depto', '=', $id_depto);
        $sql->where('dp.id_entidad', '=', $id_entidad);
        $sql->where('dp.tipo', '=', $request->tipo);
        $sql->where('dp.vigencia', '=', '1');
        $sql->select(
            'dp.id_estado_cont_depto',
            'dp.id_estado_cont',
            'c.nombre',
            'c.nombrecorto',
            'c.orden'
        );
        $sql->orderBy('c.orden', 'asc');
        $data = $sql->paginate();
        return $data;
    }

    public static function selectRequest($request)
    {
        $id_solic_reque = $request->id_solic_reque;
        $sql = DB::table('plla_solic_reque s');
        $sql->join('moises.condicion_laboral cl', 'cl.id_condicion_laboral', '=', 's.id_condicion_laboral');
        $sql->join('plla_perfil_puesto pp', 'pp.id_perfil_puesto', '=', 's.id_perfil_puesto');
        $sql->join('plla_puesto p', 'p.id_puesto', '=', 'pp.id_puesto');
        //$sql->join('conta_entidad_depto cc', 'cc.id_depto', '=', 's.id_ccosto');
        $sql->join('org_sede_area cc', 'cc.id_sedearea', '=', 's.id_ccosto');
        $sql->join('conta_entidad_depto ccc', 'ccc.id_depto', '=', 'cc.id_depto');
        $sql->join('moises.tipo_tiempo_trabajo jl', 'jl.id_tipo_tiempo_trabajo', '=', 's.id_tipo_tiempo_trabajo');
        $sql->join('org_sede_area a', 'a.id_sedearea', '=', 's.id_sedearea');
        $sql->join('org_area m', 'm.id_area', '=', 'a.id_area');
        $sql->where('s.id_solic_reque', '=', $id_solic_reque);
        $sql->select(
            's.*',
            'cl.nombre as condicion',
            'p.nombre as puesto',
            //'cc.nombre as ccosto',
            'jl.nombre as jornada',
            'jl.tiempo',
            'm.nombre as area',
            'ccc.nombre as ccosto',
            'ccc.id_depto as id_centro'
        );
        $data = $sql->get()->first();
        if ($data) {
            $candidatos = RequestData::selectCandidatos($id_solic_reque);
            $data->candidatos = $candidatos;
            $escala = RequestData::getScaleSalary($data->id_perfil_puesto, $data->id_entidad, $data->id_depto);
            $data->escala = $escala;
            $fmr = RequestData::getFMR($data->id_entidad);
            $data->fmr = $fmr;
        }
        return $data;
    }


    public static function selectCandidatos($id_solic_reque)
    {
        $sql = DB::table('plla_solic_req_candidato c');
        $sql->join('moises.persona_natural pn', 'pn.id_persona', '=', 'c.id_persona');
        $sql->join('moises.persona p', 'p.id_persona', '=', 'pn.id_persona');
        $sql->join('moises.tipo_documento td', 'td.id_tipodocumento', '=', 'pn.id_tipodocumento');
        $sql->leftJoin('plla_contrato co', 'co.id_persona', '=', 'c.id_persona');
        $sql->leftJoin('moises.situacion_educativa se', 'se.id_situacion_educativo', '=', 'pn.id_situacion_educativo');
        $sql->where("c.id_solic_reque", '=', $id_solic_reque);
        $sql->where("c.id_estado_req_cand", '=', '06');
        $sql->select(
            'c.id_solic_req_candidato',
            'p.id_persona',
            'p.nombre',
            'p.paterno',
            'p.materno',
            'pn.num_documento',
            'pn.fec_nacimiento',
            'co.id_estado_cont_depto',
            'co.sueldo',
            'co.tipo_pago',
            'co.id_escala_salarial',
            'co.menoredad',
            'co.menoredad_url',
            'co.plancap_url',
            'co.pje_fmr',
            'c.cv_url',
            'co.id_contrato',
            'se.nombre as grado',
            'co.fecha_ini',
            'co.fecha_fin',
            DB::raw("'" . url('') . "/gth/'||c.cv_url AS url"),
            DB::raw("'" . url('') . "/gth/'||co.menoredad_url AS menor_file"),
            DB::raw("'" . url('') . "/gth/'||co.plancap_url AS plan_cap_file")
        );
        $sql->orderByRaw("co.id_estado_cont_depto asc nulls first,co.id_estado_cont_depto desc");
        $data = $sql->get();
        return $data;
    }

    public static function getScaleSalary($id_perfil_puesto, $id_entidad, $id_depto)
    {
        $sql = DB::table('plla_perfil_puesto pp');
        $sql->join('plla_puesto p', 'pp.id_puesto', '=', 'p.id_puesto');
        $sql->join('plla_escala_salarial es', 'p.ID_GRUPO_ESCALA', '=', 'es.ID_GRUPO_ESCALA');
        $sql->where('es.id_depto', '=', $id_depto);
        $sql->where('es.id_entidad', '=', $id_entidad);
        $sql->where('es.id_anho', '=', '2019'); //reemplazar TO_NUMBER(TO_CHAR(SYSDATE,'YYYY'))
        $sql->where('pp.id_perfil_puesto', '=', $id_perfil_puesto);
        $sql->select(
            'es.ID_ESCALA_SALARIAL',
            'es.PUNTMINIMO',
            'es.PUNTMAXIMO',
            'es.FMR',
            DB::raw('(es.PUNTMINIMO/100)*es.FMR AS SUELDO_MIN'),
            DB::raw('(es.PUNTMAXIMO/100)*es.FMR AS SUELDO_MAX')
        );
        $data = $sql->get()->first();
        return $data;
    }

    public static function getFMR($id_entidad)
    {
        $sql = DB::table('PLLA_PARAMETROS_VALOR A');
        $sql->join('PLLA_PARAMETROS B', 'A.ID_PARAMETRO', '=', 'B.ID_PARAMETRO');
        $sql->where('a.id_anho', '=', '2019');
        $sql->where('B.CODIGO', '=', "PARAM_FMR");
        $sql->where('a.id_entidad', '=', $id_entidad);
        $sql->select(
            'a.importe'
        );
        $data = $sql->get()->first();
        return $data;
    }
}
