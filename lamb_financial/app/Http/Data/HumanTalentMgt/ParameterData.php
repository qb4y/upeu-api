<?php

namespace App\Http\Data\HumanTalentMgt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\HumanTalentMgt\ComunData;
use App\Http\Data\Setup\PersonData;

class ParameterData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public static function getYearParameter()
    {
        $query1 = DB::table(DB::raw('dual'))
            ->select(DB::raw("to_char(sysdate, 'yyyy') as id_anho"));

        $query2 = DB::table(DB::raw('dual'))
            ->select(DB::raw("to_char(case when to_char(sysdate, 'MM')='12' then  ADD_MONTHS(sysdate,4) else sysdate end ,'yyyy') as id_anho"));

        $query = DB::table('plla_parametros_valor')
            ->select(DB::raw("to_char(id_anho) as id_anho"))
            ->groupBy('id_anho')
            ->union($query1)
            ->union($query2)
            ->orderBy('id_anho', 'desc')
            ->get();
        return $query;
    }
    public static function listMyEntities($id_persona, $id_entidad)
    {
        $oQuery = DB::table('conta_entidad as a')
            ->join('conta_entidad_usuario as b', 'a.id_entidad', '=', 'b.id_entidad')
            ->where('b.id_persona', '=', $id_persona)
            ->whereraw('b.estado in (0,1)')
            ->select(
                'a.id_entidad',
                'a.nombre',
                'b.id_persona',
                'b.estado',
                DB::raw("CASE WHEN a.id_entidad = " . $id_entidad . " THEN 1 ELSE 0 END as selection")
            )
            ->orderBy('a.nombre', 'asc')
            ->get();
        return $oQuery;
    }
    public static  function Positions()
    {
        $data = DB::table('plla_puesto')
            ->where('vigencia', 1)
            ->select('id_puesto', 'nombre')
            ->orderBy('nombre', 'asc')
            ->get();
        return $data;
    }
    public static function SearchGlobalGroupScale($searchs)
    {
        $sql = DB::table('plla_grupo_escala')
            ->whereraw(ComunData::fnBuscar('nombre') . ' like ' . ComunData::fnBuscar("'%" . $searchs . "%'"))
            ->select('id_grupo_escala', 'nombre')
            ->get();
        return $sql;
    }
    public static function SearchGlobalPositions($id_sedearea, $searchs)
    {
        $sql = DB::table('plla_puesto')
            ->whereraw("id_puesto not in(select id_puesto from plla_perfil_puesto where id_sedearea = " . $id_sedearea . ")")
            ->whereraw(ComunData::fnBuscar('nombre') . ' like ' . ComunData::fnBuscar("'%" . $searchs . "%'"))
            ->select('id_puesto', 'nombre')
            ->get();
        return $sql;
    }
    public static function SearchListDepartaments($id_entidad, $id_depto, $department, $id_persona, $id_anho, $gth)
    {

        $q = DB::table('org_area as a');
        $q->join('org_sede_area b', 'b.id_area', '=', 'a.id_area');
        $q->join('conta_entidad_depto c', 'c.id_depto', '=', DB::raw('b.id_depto and b.id_entidad = c.id_entidad'));
        $q->where('b.id_entidad', $id_entidad);
        $q->whereraw(DB::raw("b.id_depto like '" . $id_depto . "%'"));
        $q->whereraw(DB::raw("(c.id_depto LIKE '%" . $department . "%' OR " . ComunData::fnBuscar('a.nombre') . ' like ' . ComunData::fnBuscar("'%" . $department . "%'") . " or c.nombre like " . ComunData::fnBuscar("'%" . $department . "%'") . ")"));
        if ($gth == 'N') {
            $q->whereraw("B.ID_SEDEAREA IN (SELECT ID_SEDEAREA FROM ORG_AREA_RESPONSABLE WHERE ID_PERSONA = " . $id_persona . " AND ID_NIVEL IN ('1','2') AND ID_ANHO = " . $id_anho . " AND ACTIVO = '1')");
        }
        $q->select(
            'a.id_area',
            'b.id_sedearea',
            'a.nombre',
            'b.id_entidad',
            'b.id_depto',
            'c.nombre as depto',
            'b.id_sede',
            DB::raw("a.nombre||' - '||c.id_depto||'-'||c.nombre as nom_comp")
        );
        $q->offset(0);
        $q->limit(20);
        $query = $q->get();
        return $query;
    }

    public static  function Level()
    {
        $data = DB::table('plla_nivel')
            ->where('vigencia', 1)
            ->select('id_nivel', 'nombre')
            ->orderBy('id_nivel', 'asc')
            ->get();
        return $data;
    }
    public static  function Grade()
    {
        $data = DB::table('plla_grado')
            ->where('vigencia', 1)
            ->select('id_grado', 'nombre')
            ->orderBy('id_grado', 'asc')
            ->get();
        return $data;
    }
    public static  function SubGrad()
    {
        $data = DB::table('plla_subgrado')
            ->where('vigencia', 1)
            ->select('id_subgrado', 'nombre')
            ->orderBy('id_subgrado', 'asc')
            ->get();
        return $data;
    }
    public static function listDeptoEntidad($id_entidad, $id_depto)
    {
        $Query = DB::table('conta_entidad_depto')
            ->where('id_entidad', '=', $id_entidad)
            ->whereraw(DB::raw("es_grupo = 1"))
            ->whereraw(DB::raw("length(id_depto) = 1"))
            ->select(
                'id_depto',
                'nombre',
                DB::raw("CASE WHEN id_depto = '" . $id_depto . "' THEN 1 ELSE 0 END as selection")
            )
            ->orderBy('id_depto', 'asc')
            ->get();

        return $Query;
    }
    public static function getYearSalaryScale($id_entidad)
    {
        $query1 = DB::table(DB::raw('dual'))
            ->select(DB::raw("to_char(sysdate, 'yyyy') as id_anho"));
        $query2 = DB::table(DB::raw('dual'))
            ->select(DB::raw("to_char(case when to_char(sysdate, 'MM')='12' then  ADD_MONTHS(sysdate,4) else sysdate end ,'yyyy') as id_anho"));
        $query = DB::table('plla_escala_salarial')
            ->select(DB::raw("to_char(id_anho) as id_anho"))
            ->where('id_entidad', $id_entidad)
            ->groupBy('id_anho')
            ->union($query1)
            ->union($query2)
            ->orderBy('id_anho', 'desc')
            ->get();
        return $query;
    }
    public static  function SearchCargoProfilePositions($id_entidad, $id_depto, $name)
    {
        // dd('xcfdf', $id_perfil_puesto);
        $query = DB::table('plla_perfil_puesto as a')
            ->join('plla_puesto as b', 'a.id_puesto', '=', 'b.id_puesto')
            ->join('org_sede_area as c', 'a.id_sedearea', '=', 'c.id_sedearea')
            ->where('c.id_entidad', $id_entidad)
            ->whereraw(DB::raw("c.id_depto like '" . $id_depto . "%'"))
            ->whereraw(ComunData::fnBuscar('b.nombre') . ' like ' . ComunData::fnBuscar("'%" . $name . "%'"))
            ->select(
                'a.id_perfil_puesto',
                'b.nombre',
                'c.id_depto'
            )
            ->orderBy('b.nombre', 'desc')
            ->get();
        return $query;
    }
    public static function getYearStaffPositions()
    {
        $query1 = DB::table(DB::raw('dual'))
            ->select(DB::raw("to_char(sysdate, 'yyyy') as id_anho"));
        $query2 = DB::table(DB::raw('dual'))
            ->select(DB::raw("to_char(case when to_char(sysdate, 'MM')='12' then  ADD_MONTHS(sysdate,4) else sysdate end ,'yyyy') as id_anho"));
        $query = DB::table('plla_perfil_puesto_anho')
            ->select(DB::raw("to_char(id_anho) as id_anho"))
            ->groupBy('id_anho')
            ->union($query1)
            ->union($query2)
            ->orderBy('id_anho', 'desc')
            ->get();
        return $query;
    }
    public static function getControlsPersons()
    {
        $query = DB::table(DB::raw('moises.tipo_control_personal'))
            ->where('vigencia', 1)
            ->select('id_tipo_control_personal', 'nombre')
            ->orderBy('id_tipo_control_personal', 'asc')
            ->get();
        return $query;
    }
    public static function getAutonomyPosition($id_perfil_puesto)
    {
        $query = DB::table('plla_automia_puesto as a')
            ->where('vigencia', 1)
            ->select(
                'a.id_automia_puesto',
                'a.comentario',
                DB::raw("(SELECT COUNT(*) FROM PLLA_PERFIL_PUESTO B WHERE B.ID_AUTOMIA_PUESTO=A.ID_AUTOMIA_PUESTO AND B.ID_PERFIL_PUESTO = '" . $id_perfil_puesto . "') AS SELE")
            )
            ->orderBy('a.id_automia_puesto', 'asc')
            ->get();
        return $query;
    }
    public static function getUbicationFADM()
    {
        // dd('fff');
        $query = DB::table('plla_ubicacionofadm')

            ->where('vigencia', 1)
            ->select('id_ubicacionofadm', 'nombre')
            ->orderBy('id_ubicacionofadm', 'asc')
            ->get();
        return $query;
    }

    public static function getResponsability($id_perfil_puesto)
    {
        // dd('fff');
        $query = DB::table('plla_responsabilidad as a')
            ->leftjoin('plla_perfil_puesto_nivresp as b', 'a.id_responsabilidad',  '=', DB::raw("b.id_responsabilidad and id_perfil_puesto = " . $id_perfil_puesto))
            ->where('a.vigencia', 1)

            ->select('a.id_responsabilidad', 'b.id_perfil_puesto', 'a.nombre', 'a.comentario', 'b.id_tipo_nivel_resp')
            ->orderBy('a.id_responsabilidad', 'asc')
            ->get();
        return $query;
    }

    public static function getLevelIntelligence()
    {
        // dd('fff');
        $query = DB::table('plla_tipo_nivel')
            ->where('vigencia', 1)
            ->select('id_tipo_nivel', 'nombre')
            ->orderBy('id_tipo_nivel', 'asc')
            ->get();
        return $query;
    }
    public static function getLevelResponsability()
    {
        // dd('fff');
        $query = DB::table('plla_tipo_nivel_resp')
            ->where('vigencia', 1)
            ->select('id_tipo_nivel_resp', 'nombre')
            ->orderBy('id_tipo_nivel_resp', 'asc')
            ->get();
        return $query;
    }

    public static function getSituationEducation()
    {

        $query = DB::table('moises.situacion_educativa')
            ->where('vigencia', 1)
            ->select('id_situacion_educativo', 'nombre_corto', 'tipo', 'condicion')
            ->get();
        return $query;
    }

    public static function getExpProf()
    {
        $query = DB::table('plla_exp_laboral')
            ->where('vigencia', 1)
            ->select('id_exp_laboral', 'nombre', 'desde', 'hasta', 'vigencia')
            ->get();
        return $query;
    }

    public static function getTypeEspdip()
    {

        $query = DB::table('plla_tipo_espdip')
            ->where('vigencia', 1)
            ->select('id_tipo_espdip', 'nombre')
            ->get();
        return $query;
    }
    public static function searchProfesionOcupation($searchs)
    {
        $sql = DB::table('moises.profesiones')
            ->whereraw(ComunData::fnBuscar('nombre') . ' like ' . ComunData::fnBuscar("'%" . $searchs . "%'"))
            ->select('id_profesion', 'nombre')
            ->get();
        return $sql;
    }
    public static function getCarrera()
    {
        return DB::table('moises.carrera_profesional')
            ->select('id_carrera_profesional', 'nombre')
            ->get();
    }
    public static function getTypeLenguages()
    {
        $data = DB::table('moises.tipo_idioma')
            ->select('id_tipoidioma', 'nombre', 'tipo', 'codsunedo')
            ->where('vigencia', 1)
            ->get();
        return $data;
    }
    public static function getOfimatica()
    {
        $off = DB::table('plla_conoci_inform')
            ->where('vigencia', 1)
            ->select('id_conoci_inform', 'nombre')
            ->get();
        return $off;
    }
    public static function listSupervisionTo($id_perfil_puesto)
    {
        $query = DB::table('plla_puesto as a')
            ->join('plla_perfil_puesto as b', 'a.id_puesto', '=', 'b.id_puesto')
            ->where('b.id_perfil_puesto_jefe', $id_perfil_puesto)
            ->select('b.id_perfil_puesto', 'a.id_puesto', 'a.nombre')
            ->orderBy('a.nombre', 'asc')
            ->get();
        return $query;
    }
    public static function getRequirements()
    {
        $query = DB::table('plla_requisitos')
            ->where('vigencia', 1)
            ->select('id_requisitos', 'nombre')
            ->get();
        return $query;
    }
    public static function getPeriodoHolidays($request)
    {

        $id_entidad = $request->query('id_entidad');
        $adelantadas = $request->query('no_mostrar_id_estado');
        // dd($adelantadas);
        $q = DB::table('plla_periodo_vac');
        $q->where('id_entidad', $id_entidad);
        if ($adelantadas) {
            $q->whereNotIn('id_estado_periodo_vac', [$adelantadas]);
        }
        $q->select('id_periodo_vac', 'nombre', 'min_dias_periodo1', 'min_dias_periodo2', 'anho_inicio', 'id_estado_periodo_vac');
        $q->orderBy('anho_inicio', 'desc');
        $query = $q->get();
        return $query;
    }
    public static function listTrabajadorHolidays($request)
    {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_periodo_vac = $request->id_periodo_vac;
        $id_sedearea = $request->id_sedearea;
        $estado = $request->estado;
        $persona = $request->persona;
        $per_page = $request->per_page;
        $adelanto = $request->adelanto;

        $q = DB::table('moises.vw_trabajador a');
        $q->join('eliseo.plla_periodo_vac_trab x', 'a.id_persona', '=', 'x.id_persona'); //agrgado recientemente
        $q->join('eliseo.plla_estado_vac_trab ev', 'x.id_estado_vac_trab', '=', 'ev.id_estado_vac_trab');
        $q->join('org_sede_area b', 'a.id_sedearea', '=', 'b.id_sedearea');
        $q->join('moises.tipo_documento c', 'a.id_tipodocumento', '=', 'c.id_tipodocumento');
        $q->join('moises.situacion_trabajador d', 'a.id_situacion_trabajador', '=', 'd.id_situacion_trabajador');
        $q->join('moises.condicion_laboral e', 'a.id_condicion_laboral', '=', 'e.id_condicion_laboral');
        $q->join('moises.tipo_tiempo_trabajo f', 'a.id_tipo_tiempo_trabajo', '=', 'f.id_tipo_tiempo_trabajo');
        $q->join('org_area g', 'b.id_area', '=', 'g.id_area');
        $q->leftjoin('plla_tipo_horario th', 'th.id_tipo_horario', '=', 'a.id_tipo_horario');
        $q->leftjoin('moises.tipo_control_personal tc', 'tc.id_tipo_control_personal', '=', 'a.id_tipo_control_personal');
        $q->where('b.id_entidad', $id_entidad);
        $q->whereraw("b.id_depto like '" . $id_depto . "%'");
        $q->where('x.id_periodo_vac', $id_periodo_vac);
        if (strlen($id_sedearea) > 0) {
            $q->where('a.id_sedearea', $id_sedearea);
        }
        if (strlen($estado) > 0) {
            if ($adelanto == 'S') {
                $q->whereraw("(ev.id_estado_vac_trab='" . $estado . "' or x.id_periodo_vac_trab in(
                    select r.id_periodo_vac_trab from plla_rol_vacacional r, plla_periodo_vac_trab pt 
                    where r.id_periodo_vac_trab=pt.id_periodo_vac_trab
                    and pt.id_periodo_vac=" . $id_periodo_vac . "
                    and r.id_tipo_rol_vac='A'
                    ))");
            } else {
                $q->where('ev.id_estado_vac_trab', $estado);
            }
        }
        if (strlen($persona) > 0) {
            $q->whereraw("(upper(a.nombre) like upper('%" . $persona . "%')
        or upper(a.nombre ||' ' || a.paterno ) like upper('%" . $persona . "%')
        or upper(a.paterno ||' ' || a.materno ) like upper('%" . $persona . "%')
        or a.num_documento like '%" . $persona . "%')");
        }
        $q->select(
            'a.id_persona',
            'b.id_entidad',
            'b.id_depto',
            DB::raw("(a.nombre || ' ' || a.paterno || ' ' || a.materno) as nombres"),
            'g.nombre as nombre_area',
            'a.num_documento',
            'c.siglas',
            DB::raw("to_char(a.fecha_inicio, 'YYYY-MM-DD') as fecha_inicio"),
            DB::raw("to_char(a.fecha_fin_previsto, 'YYYY-MM-DD') as fecha_fin_previsto"),
            DB::raw("to_char(a.fecha_fin_efectivo, 'YYYY-MM-DD') as fecha_fin_efectivo"),
            'th.nombre as tipo_horario',
            'tc.nombre as tipo_control_personal',
            'd.nombre as nombre_corto',
            'd.id_situacion_trabajador',
            'e.nombre as nombre_condicion',
            'f.nombre as tiempo_trabajo',
            DB::raw("to_char(x.periodo_ini, 'YYYY-MM-DD') as periodo_ini"),
            DB::raw("to_char(x.periodo_fin, 'YYYY-MM-DD') as periodo_fin"),
            'x.total_dias',
            'x.total_dias_efect',
            'ev.nombre as estado',
            'ev.id_estado_vac_trab',
            'x.id_periodo_vac',
            DB::raw("(SELECT nvl(count(CONFIRMACION_SALIDA), 0) from eliseo.PLLA_ROL_VACACIONAL rv
            where rv.ID_PERIODO_VAC_TRAB = x.ID_PERIODO_VAC_TRAB
            and rv.CONDICION = 'P'
            and rv.CONFIRMACION_SALIDA in ('1')) as firmados_salida"),
            DB::raw("(SELECT nvl(count(CONFIRMACION_RETORNO), 0)  from eliseo.PLLA_ROL_VACACIONAL rv
            where rv.ID_PERIODO_VAC_TRAB = x.ID_PERIODO_VAC_TRAB
            and rv.CONDICION = 'P'
            and rv.CONFIRMACION_RETORNO in ('1')) as firmados_retorno"),
            DB::raw("(SELECT count(1) from eliseo.PLLA_ROL_VACACIONAL rv where rv.ID_PERIODO_VAC_TRAB = x.ID_PERIODO_VAC_TRAB and CONDICION = 'P') as total_firmmar")

        );
        $q->orderBy('a.nombre', 'asc');
        $data = $q->paginate((int) $per_page);
        return $data;
    }
    public static function listCompetences($tipo, $agregados)
    {
        $array_id = explode(',', $agregados);
        $query = DB::table('plla_competencia as a')
            ->where('a.tipo', $tipo)
            ->where('a.vigencia', 1)
            ->whereNotIn('a.id_competencia', $array_id)
            ->select('a.id_competencia', 'a.nombre', 'a.tipo', DB::raw("'*' as id_tipo_nivel_comp, '0' as puntaje, '' as checked"))
            // ->orderBy('a.nombre', 'asc')
            ->get();
        // dd($query, $agregados);
        return $query;
    }
    public static function getTypeLevelCompetences()
    {
        $query = DB::table('plla_tipo_nivel_comp as a')
            ->where('a.vigencia', 1)
            ->select('a.id_tipo_nivel_comp', 'a.nombre')
            ->orderBy('a.nombre', 'asc')
            ->get();
        return $query;
    }


    /// Recoger data escencial de recursos humanos
    public static function getSexo()
    {
        $query = DB::table('moises.tipo_sexo')
            ->where('vigencia', 1)
            ->get();
        return $query;
    }
    // GET PARA DOCUMENTOS
    public static function getTypeDocument()
    {
        $query = DB::table('moises.tipo_documento TD')
            ->select('td.id_tipodocumento AS id', 'td.nombre AS nombre')
            ->orderBy('nombre', 'asc')
            ->get();
        return $query;
    }
    // estdo civil 
    public static function getCivilStatus()
    {
        $query = DB::table('tipo_estado_civil tec')
            ->where('tec.estado_psto', 1)
            ->select('tec.id_tipoestadocivil as id_civil', 'tec.nombre as nombre')
            ->orderBy('nombre', 'asc')
            ->get();
        return $query;
    }
    // GET SANGRE
    public static function getSangre()
    {
        $query = DB::table('moises.tipo_sangre')
            ->orderBy('nombre', 'asc')
            ->get();
        return $query;
    }
    // Tipo de direcciones
    public static function getTypeDirecction()
    {
        $query = DB::table('moises.tipo_direccion td')
            ->whereraw('td.id_tipodireccion in (4,5)')
            ->select('td.id_tipodireccion as id', 'td.nombre')
            ->orderBy('td.nombre', 'asc')
            ->get();
        return $query;
    }
    // Type Zone
    public static function getZone()
    {
        $query = DB::table('moises.tipo_zona zo')
            ->select('zo.id_tipozona as id', 'zo.nombre', 'zo.nombrecorto')
            ->where('zo.vigencia', 1)
            ->orderBy('zo.nombre', 'asc')
            ->get();
        return $query;
    }
    // Type Via
    public static function getVia()
    {
        $query = DB::table('moises.tipo_via ve')
            ->select('ve.id_tipovia as id', 've.nombre', 've.nombrecorto')
            ->where('ve.vigencia', 1)
            ->orderBy('ve.nombre', 'asc')
            ->get();
        return $query;
    }

    // Search Pais
    public static function SearchPais($searchs)
    {
        $sql = DB::table('tipo_pais TP')
            ->whereraw(ComunData::fnBuscar('TP.nombre') . ' like ' . ComunData::fnBuscar("'%" . $searchs . "%'"))
            ->select('tp.id_tipopais AS id_pais', 'tp.nombre AS nombre', 'tp.iso_a3 as iso')
            ->orderBy('nombre', 'asc')
            ->get();
        return $sql;
    }

    // Search Ubigueo
    public static function getDepto()
    {
        $query = DB::table('moises.ubigueo dt')
            ->where('ditto', '00')
            ->where('pvcia', '00')
            ->orderBy('nombre', 'asc')
            ->select('dt.depto as id', 'dt.nombre')
            ->get();
        return $query;
    }


    // Search Provincia
    public static function getProv($id_depto)
    {
        $query = DB::table('moises.ubigueo pv')
            ->where('depto', $id_depto)
            ->where('ditto', '00')
            ->where('pvcia', '<>', '00')
            ->select('pv.pvcia as id', 'pv.nombre')
            ->orderBy('pv.nombre', 'asc')
            ->get();
        return $query;
    }

    // Search Distrito
    public static function getDistri($id_depto, $id_distrito)
    {
        $query = DB::table('moises.ubigueo ub')
            ->where('ub.depto', $id_depto)
            ->where('ub.ditto', '<>', '00')
            ->where('ub.pvcia',  $id_distrito)
            ->select('ub.id_ubigeo as id', 'ub.nombre')
            ->orderBy('ub.nombre', 'asc')
            ->get();
        // dd($query);
        return $query;
    }

    // Search Tipo Seccion
    public static function getSeccion()
    {
        $query = DB::table('moises.tipo_seccion sec')
            ->select('sec.id_tiposeccion as id', 'sec.nombre')
            ->where('sec.vigencia', 1)
            ->orderBy('sec.nombre', 'asc')
            ->get();
        return $query;
    }

    // scripts para P2

    // situacion educativa
    public static function getSituacion()
    {
        $query = DB::table('moises.situacion_educativa se')
            ->select('se.id_situacion_educativo', 'se.nombre', 'se.condicion', 'se.tipo')
            ->orderBy('se.id_situacion_educativo', 'asc')
            ->get();
        return $query;
    }

    // tipo institucion
    public static function getTypeInstitucion()
    {
        $query = DB::table('moises.tipo_institucion')
            ->orderBy('nombre', 'asc')
            ->get();
        return $query;
    }

    // Buscador de regimen
    public static function getTypeRegimen()
    {
        $query = DB::table('moises.regimen_institucion')
            ->orderBy('nombre', 'asc')
            ->get();
        return $query;
    }

    // Busca instituciones
    public static function SearchInstitucion($searchs, $regimen, $tipo_ins)
    {
        $sql = DB::table('moises.instituciones ins')
            ->whereraw(ComunData::fnBuscar('ins.nombre') . ' like ' . ComunData::fnBuscar("'%" . $searchs . "%'"))
            ->select('ins.id_instituicion', 'ins.nombre')
            ->where('ins.id_regimen_inst', $regimen)
            ->where('ins.id_tipo_ins', $tipo_ins)
            ->where('ins.vigencia', '1')
            ->orderBy('ins.nombre', 'asc')
            ->get();
        return $sql;
    }


    // buscador de carreras
    public static function SearchCarrera($searchs)
    {
        $sql = DB::table('moises.carrera_profesional cp')
            ->whereraw(ComunData::fnBuscar('cp.nombre') . ' like ' . ComunData::fnBuscar("'%" . $searchs . "%'"))
            ->select('cp.id_carrera_profesional', 'cp.nombre')
            ->orderBy('cp.nombre', 'asc')
            ->get();
        return $sql;
    }
    public static  function listGrupoEscala($id_entidad, $id_depto, $id_anho, $id_nivel)
    {
        $q = DB::table('plla_grupo_escala as a');
        $q->join('plla_nivel as b', 'b.id_nivel', '=', 'a.id_nivel');
        $q->join('plla_grado as c', 'c.id_grado', '=', 'a.id_grado');
        $q->join('plla_subgrado as d', 'd.id_subgrado', '=', 'a.id_subgrado');
        $q->where('a.vigencia', 1);
        $q->where('a.id_nivel', $id_nivel);
        $q->whereraw("id_grupo_escala not in(
                        select id_grupo_escala from plla_escala_salarial
                        where id_entidad=" . $id_entidad . "
                            and id_depto='" . $id_depto . "'
                            and id_anho=" . $id_anho . "
                        )");
        $q->select('a.*', 'b.nombre as nivel', 'c.nombre as grado', 'd.nombre as subgrado', DB::raw("'' as opcion,'0' as opcionreal"));
        $q->orderBy('a.id_nivel', 'asc');
        $q->orderBy('a.id_grado', 'asc');
        $q->orderBy('a.id_subgrado', 'asc');
        $q->orderBy('a.nombre', 'asc');

        $query = $q->get();
        return $query;
    }
    public static function listCompetenciasLb($nombre, $per_page)
    {
        $query = DB::table('plla_competencia')
            ->whereraw(ComunData::fnBuscar('nombre') . ' like ' . ComunData::fnBuscar("'%" . $nombre . "%'"))
            ->select('id_competencia', 'nombre', 'tipo', 'vigencia', DB::raw("(case when tipo = 'O' then 'ORGANIZACIONAL' when tipo = 'F' then 'FUNCIONAL' else '' end) as tipo_com"))
            ->orderBy('nombre', 'asc')
            // ->get();
            ->paginate((int) $per_page);
        return $query;
    }
    public static function getGroupCompetences($id_tipo)
    {
        $query = DB::table('plla_grupo_compentencia as a')
            ->where('a.vigencia', 1)
            ->where('a.tipo', $id_tipo)
            ->select(
                'a.id_grupo_compentencia',
                'a.nombre',
                'a.tipo as tipo_prefijo',
                DB::raw("(CASE WHEN a.tipo = 'O' THEN 'ORGANIZACIONAL' WHEN a.tipo = 'F' THEN 'FUNCIONAL' ELSE '' END) as tipo")
            )
            ->orderBy('a.nombre', 'asc')
            ->get();
        return $query;
    }
    public static function searchComitions($search)
    {
        $sql = DB::table('plla_comisiones')
            ->whereraw(ComunData::fnBuscar('nombre') . ' like ' . ComunData::fnBuscar("'%" . $search . "%'"))
            ->select('id_comisiones', 'nombre')
            ->orderBy('nombre', 'asc')
            ->get();
        return $sql;
    }
    public static function searchProcess($search)
    {
        $sql = DB::table('plla_procesos')
            ->whereraw(ComunData::fnBuscar('nombre') . ' like ' . ComunData::fnBuscar("'%" . $search . "%'"))
            ->select('id_procesos', 'nombre')
            ->orderBy('nombre', 'asc')
            ->get();
        return $sql;
    }
    public static function salaryScaleLevel($id_perfil_puesto)
    {
        $query = DB::table('plla_perfil_puesto as a')
            ->join('plla_puesto as b', 'a.id_puesto', '=', 'b.id_puesto')
            ->join('plla_grupo_escala as c', 'b.id_grupo_escala', '=', 'c.id_grupo_escala')
            ->join('plla_nivel as d', 'c.id_nivel', '=', 'd.id_nivel')
            ->where('a.id_perfil_puesto', $id_perfil_puesto)
            ->select('a.id_perfil_puesto', 'd.nombre')
            ->orderBy('d.nombre', 'asc')
            ->get()->shift();
        return $query;
    }

    // tipo de religion que tiene
    public static function getTypeReligion()
    {
        $query = DB::table('moises.tipo_religion')
            ->orderBy('nombre', 'asc')
            ->get();
        return $query;
    }

    // tipo de autoridad 
    public static function getTypeAutoridad()
    {
        $query = DB::table('moises.tipo_autoridad_iglesia')
            ->orderBy('nombre', 'asc')
            ->get();
        return $query;
    }

    // Buscador de persona 
    public static function SearchPersona($searchs, $sexo)
    {
        $sql = DB::table('moises.persona p');
        $sql->join('moises.persona_natural pn', 'p.id_persona', '=', 'pn.id_persona');
        $sql->join('moises.tipo_documento td', 'td.id_tipodocumento', '=', 'pn.id_tipodocumento');
        $sql->leftjoin('moises.tipo_pais tp', 'tp.id_tipopais', '=', 'pn.id_tipopais');
        $sql->whereraw("(upper(replace(concat(concat(p.nombre,p.paterno),p.materno),' ','')) like upper(replace('%" . $searchs . "%',' ','')) 
        or upper(replace(concat(concat(regexp_substr(p.nombre ,'[^ ]+',1,1),p.paterno),p.materno),' ','')) like upper(replace('%" . $searchs . "%',' ',''))
        or upper(replace(concat(concat(regexp_substr(p.nombre ,'[^ ]+',1,2),p.paterno),p.materno),' ','')) like upper(replace('%" . $searchs . "%',' ',''))
        or (pn.num_documento like '%" . $searchs . "%'))");
        if ($sexo > 0) {
            $sql->whereraw(DB::raw('pn.sexo ' . '= ' . $sexo));
        }
        $sql->whereraw('rownum <= 10');
        $sql->select(
            'p.id_persona',
            DB::raw(" p.paterno ||' '|| p.materno ||' '||p.nombre as nombre"),
            'p.nombre as name_p',
            'p.paterno as a_paterno',
            'p.materno as a_materno',
            'pn.fec_nacimiento',
            'td.id_tipodocumento',
            'td.siglas',
            'pn.num_documento',
            'pn.sexo',
            'pn.telefono',
            'pn.celular',
            'pn.id_tipopais',
            'pn.correo',
            'tp.nombre as pais'
        );
        $sql->orderBy('p.nombre', 'asc');
        $data = $sql->get();
        return $data;
    }

    public static function SearchPersonaNoWorker($searchs, $sexo)
    {
        $sql = DB::table('moises.persona p');
        $sql->join('moises.persona_natural pn', 'p.id_persona', '=', 'pn.id_persona');
        $sql->leftjoin('moises.persona_natural_trabajador t', 't.id_persona', '=', 'pn.id_persona');
        $sql->leftjoin('org_sede_area sa', 'sa.id_sedearea', '=', 't.id_sedearea');
        $sql->leftjoin('org_area a', 'a.id_area', '=', 'sa.id_area');
        $sql->join('moises.tipo_documento td', 'td.id_tipodocumento', '=', 'pn.id_tipodocumento');
        $sql->leftjoin('moises.tipo_pais tp', 'tp.id_tipopais', '=', 'pn.id_tipopais');
        $sql->leftJoin('MOISES.situacion_educativa se', 'se.id_situacion_educativo', '=', 'pn.id_situacion_educativo');
        $sql->whereraw("(upper(replace(concat(concat(p.nombre,p.paterno),p.materno),' ','')) like upper(replace('%" . $searchs . "%',' ','')) 
        or upper(replace(concat(concat(regexp_substr(p.nombre ,'[^ ]+',1,1),p.paterno),p.materno),' ','')) like upper(replace('%" . $searchs . "%',' ',''))
        or upper(replace(concat(concat(regexp_substr(p.nombre ,'[^ ]+',1,2),p.paterno),p.materno),' ','')) like upper(replace('%" . $searchs . "%',' ',''))
        or (pn.num_documento like '%" . $searchs . "%'))");
        if ($sexo > 0) {
            $sql->whereraw(DB::raw('pn.sexo ' . '= ' . $sexo));
        }
        //$sql->whereraw('(t.id_situacion_trabajador=0 or t.id_situacion_trabajador is null)');
        $sql->whereraw('rownum <= 10');
        $sql->select(
            'p.id_persona',
            DB::raw(" p.paterno ||' '|| p.materno ||' '||p.nombre as nombre"),
            'p.nombre as name_p',
            'p.paterno as a_paterno',
            'p.materno as a_materno',
            'pn.fec_nacimiento',
            'td.id_tipodocumento',
            'td.siglas',
            'pn.num_documento',
            'pn.sexo',
            'pn.telefono',
            'pn.celular',
            'pn.id_tipopais',
            'pn.correo',
            'tp.nombre as pais',
            't.id_situacion_trabajador',
            "t.observado",
            'a.nombre as nom_area',
            'pn.id_situacion_educativo',
            'se.nombre as situacion_educativo'
        );
        $sql->orderBy('p.nombre', 'asc');
        $data = $sql->get();
        return $data;
    }

    // Buscar vinculo familiar
    public static function getVinculoPersona()
    {
        $sql = DB::table('moises.tipo_vinculo_familiar vf')
            ->where('vf.vigencia', '1')
            ->select('vf.id_tipo_vinculo_familiar as id', 'vf.nombre')
            ->orderBy('vf.nombre', 'asc')
            ->get();
        return $sql;
    }
    // Buscar tipo doc sust vf
    public static function getDocSusVf()
    {
        $sql = DB::table('moises.tipo_doc_sust_vf ds')
            ->where('ds.vigencia', '1')
            ->select('ds.id_tipo_doc_sust_vf as id', 'ds.nombre')
            ->orderBy('ds.nombre', 'asc')
            ->get();
        return $sql;
    }

    // Buscar tipo doc sust vf
    public static function getMotivoBaja()
    {
        $sql = DB::table('moises.tipo_motivo_baja_dh dh')
            ->where('dh.vigencia', '1')
            ->select('dh.id_tipo_motivo_baja_dh as id', 'dh.nombre')
            ->orderBy('dh.nombre', 'asc')
            ->get();
        return $sql;
    }

    // Buscar tipo doc sust vf
    public static function getDiscapacidad()
    {
        $sql = DB::table('moises.tipo_discapacidad d')
            ->where('d.vigencia', '1')
            ->select('d.id_tipo_discapacidad', 'd.nombre')
            ->orderBy('d.nombre', 'asc')
            ->get();
        return $sql;
    }
    // get estado periodo vac
    public static function getEstadoPeriodoVac()
    {
        $query = DB::table('plla_estado_periodo_vac a')
            ->select('a.id_estado_periodo_vac', 'a.nombre')
            ->orderBy('a.nombre', 'asc')
            ->get();
        return $query;
    }

    // Buscar id_periodo_remu
    public static function getPeriodoRemuneracion()
    {
        $sql =  DB::table('eliseo.plla_periodo_remu ppr')
            ->where('ppr.vigencia', '1')
            ->select('ppr.id_periodo_remu as id', 'ppr.nombre')
            ->orderBy('ppr.nombre', 'asc')
            ->get();
        return $sql;
    }
    // Buscar getCtaBanco
    public static function getCtaBanco()
    {
        $sql =  DB::table('eliseo.tipo_cta_banco tcb')
            ->select('tcb.id_tipoctabanco as id', 'tcb.nombre')
            ->orderBy('tcb.nombre', 'asc')
            ->get();
        return $sql;
    }
    public static function getTipoSuspension()
    {
        $sql =  DB::table('plla_tipo_suspension')
            ->where('vigencia', 1)
            ->select(
                'id_tipo_suspension',
                'nombre_corto',
                'periodo',
                'tipo',
                'codigo',
                'codsunat',
                'cant_dias',
                DB::raw("case when tipo = 'SP' THEN 'S.P.' WHEN tipo = 'SI' THEN 'S.I.' ELSE '' END as tipo_suspension")
            )
            ->orderBy('tipo', 'desc')
            ->orderBy('nombre_corto', 'asc')
            ->get();
        return $sql;
    }

    public static function SearchTrabajador($id_sedearea, $id_entidad, $searchs)
    {
        $q = DB::table('moises.vw_trabajador p');
        $q->join('org_sede_area b', 'p.id_sedearea', '=', 'b.id_sedearea');
        $q->join('moises.persona_natural as c', 'p.id_persona', '=', 'c.id_persona');
        $q->where('b.id_entidad', $id_entidad);
        if (strlen($id_sedearea) > 0) {
            $q->where('b.id_sedearea', $id_sedearea);
        }
        if (strlen($searchs) > 0) {
            $q->whereraw("(upper(p.nombre) like upper('%" . $searchs . "%')
        or upper(p.nombre ||' ' || p.paterno ) like upper('%" . $searchs . "%')
        or upper(p.paterno ||' ' || p.materno ) like upper('%" . $searchs . "%')
        or c.num_documento like '%" . $searchs . "%')");
        }
        $q->select('p.id_persona', DB::raw("(p.nombre ||' '|| p.paterno ||' '||p.materno) nombres"), 'c.num_documento');
        $data = $q->get();
        //  ->get();
        return $data;
    }

    // Buscar id_tipo_comision_pens
    public static function getTypeComision()
    {
        $sql =  DB::table('eliseo.plla_tipo_comision_pens pcp')
            ->where('pcp.vigencia', '1')
            ->select(
                'pcp.id_tipo_comision_pens as id',
                DB::raw("pcp.tipo ||' - '|| pcp.nombre as nombre")
            )
            ->orderBy('pcp.nombre', 'asc')
            ->get();
        return $sql;
    }

    //tipo de cincepto
    public static function getTypeConcept()
    {
        $sql =  DB::table('plla_tipo_concepto')
            ->where('vigencia', 1)
            ->select('id_tipo_concepto', 'nombre', 'nombrecorto')
            ->orderBy('orden', 'asc')
            ->get();
        return $sql;
    }
    //tipo de cincepto sunat
    public static function getTypeSunatConcept($id_tipo_concepto)
    {
        $sql =  DB::table('plla_tipo_concepto_sunat as a')
            ->join('plla_tipo_concepto as b', 'b.id_tipo_concepto', '=', 'a.id_tipo_concepto')
            ->where('a.id_tipo_concepto', $id_tipo_concepto)
            ->where('a.vigencia', 1)
            ->select('a.id_tipo_concepto_sunat', 'b.id_tipo_concepto', 'a.nombre', 'b.nombrecorto')
            ->orderBy('a.id_tipo_concepto_sunat', 'asc')
            ->orderBy('a.orden', 'asc')
            ->get();
        return $sql;
    }
    //tipo de cincepto
    public static function getTypeApsConcept()
    {
        $sql =  DB::table('tipo_concepto_planilla')
            ->select('id_tipoconceptoaps', 'nombre', 'signo')
            ->orderBy('nombre', 'asc')
            ->get();
        return $sql;
    }
    // estado lica per
    public static function getEstadoLicaPer()
    {
        $data = DB::table('plla_estado_lica_per')
            ->where('vigencia', 1)
            ->select('id_estado_lica_per', 'nombre', 'nombrecorto', 'vigencia')
            ->orderBy('id_estado_lica_per', 'desc')
            ->get();
        return $data;
    }
    public static function SearchGlobalPositionsFiltrado($id_sedearea, $searchs)
    {
        // dd('dd', $id_sedearea, $searchs);
        $sql = DB::table('plla_puesto as a')
            ->join('plla_perfil_puesto as b', 'a.id_puesto', '=', 'b.id_puesto')
            ->join('org_sede_area as d', 'b.id_sedearea', '=', 'd.id_sedearea')
            ->join('org_area as e', 'd.id_area', '=', 'e.id_area')
            ->whereraw("b.id_sedearea not in(" . $id_sedearea . ")")
            ->whereraw(ComunData::fnBuscar('a.nombre') . ' like ' . ComunData::fnBuscar("'%" . $searchs . "%'") . " or d.id_depto like '%" . $searchs . "%'")
            // ->whereraw()
            // ->whereraw("d.id_depto like '%".$searchs."%'")
            ->select('a.id_puesto', 'a.nombre', 'b.id_perfil_puesto', 'd.id_depto', 'e.nombre as nombre_depto')
            ->get();
        return $sql;
    }
    public static function getTypePayroll($id_entidad, $id_grupoo_planilla)
    {
        $data = DB::table('plla_tipo_planilla as a')
            ->join('plla_planilla_entidad as b', 'b.id_tipo_planilla', '=', 'a.id_tipo_planilla')
            ->where('b.id_grupo_planilla', $id_grupoo_planilla)
            ->where('b.id_entidad', $id_entidad)
            ->where('a.vigencia', 1)
            ->select('b.id_planilla_entidad', 'a.id_tipo_planilla', 'a.nombre', 'a.codigo', 'a.nombrecorto', 'a.orden', 'a.vigencia')
            ->orderBy('a.orden', 'asc')
            ->get();
        return $data;
    }
    public static function getGroupPayroll()
    {
        $data = DB::table('plla_grupo_planilla')
            ->where('vigencia', 1)
            ->select('id_grupo_planilla', 'codigo', 'nombre', 'nombrecorto', 'orden', 'vigencia')
            ->orderBy('orden', 'asc')
            ->get();
        return $data;
    }

    public static function SearchTrabajadorOverTime($id_sedearea, $id_entidad, $searchs)
    {
        $q = DB::table('moises.vw_trabajador p');
        $q->join('org_sede_area b', 'p.id_sedearea', '=', 'b.id_sedearea');
        $q->join('moises.persona_natural as c', 'p.id_persona', '=', 'c.id_persona');
        $q->join('plla_perfil_puesto as d', 'p.id_sedearea', '=', DB::raw("d.id_sedearea and p.id_puesto = d.id_puesto"));
        $q->where('b.id_entidad', $id_entidad);
        $q->where('d.id_tipo_control_personal', 3);
        if (strlen($id_sedearea) > 0) {
            $q->where('b.id_sedearea', $id_sedearea);
        }
        if (strlen($searchs) > 0) {
            $q->whereraw("(upper(p.nombre) like upper('%" . $searchs . "%')
        or upper(p.nombre ||' ' || p.paterno ) like upper('%" . $searchs . "%')
        or upper(p.paterno ||' ' || p.materno ) like upper('%" . $searchs . "%')
        or c.num_documento like '%" . $searchs . "%')");
        }
        $q->select('p.id_persona', DB::raw("(p.nombre ||' '|| p.paterno ||' '||p.materno) nombres"), 'c.num_documento');
        $data = $q->get();
        //  ->get();
        return $data;
    }

    public static function getEstadoOvertime()
    {
        $data = DB::table('plla_estado_sobretiempo')
            ->where('vigencia', 1)
            ->select('id_estado_sobretiempo', 'nombre', 'nombrecorto', 'vigencia')
            ->orderBy('id_estado_sobretiempo', 'desc')
            ->get();
        return $data;
    }

    public static function getMonths()
    {
        $data = DB::table('conta_mes')
            ->select('id_mes', 'nombre', 'siglas')
            ->orderBy('id_mes', 'asc')
            ->get();
        return $data;
    }
    public static function getMonthlyPaymentType($id_entidad)
    {
        $data = DB::table('plla_tipo_pago_mensual as a')
            ->join('plla_concepto_planilla as b', 'b.id_concepto_planilla', '=', 'a.id_concepto_planilla')
            ->join('plla_tipo_concepto as t', 't.id_tipo_concepto', '=', 'b.id_tipo_concepto')
            ->where('a.id_entidad', $id_entidad)
            ->where('a.vigencia', 1)
            ->select(
                'a.id_tipo_pago_mensual',
                't.nombre as tipo_concepto',
                't.id_tipo_concepto',
                'a.nombre',
                'a.id_concepto_planilla',
                'b.nombre as concepto_planilla',
                'b.codigo',
                'a.consumo_comb',
                'a.mantenimiento',
                'a.seguro',
                'a.depreciacion',
                'a.combustible',
                'a.tipo',
                DB::raw("row_number() OVER (ORDER BY t.orden,a.nombre ) as items")
            )
            ->orderBy('t.orden', 'asc')
            ->orderBy('a.nombre', 'asc')
            ->get();
        return $data;
    }
    public static function getYearAssignMonth($id_entidad)
    {
        $query1 = DB::table(DB::raw('dual'))
            ->select(DB::raw("to_char(sysdate, 'yyyy') as id_anho"));
        $query2 = DB::table(DB::raw('dual'))
            ->select(DB::raw("to_char(case when to_char(sysdate, 'MM')='12' then  ADD_MONTHS(sysdate,4) else sysdate end ,'yyyy') as id_anho"));
        $query = DB::table('plla_proc_planilla')
            ->where('id_entidad', $id_entidad)
            ->select(DB::raw("to_char(id_anho) as id_anho"))
            ->groupBy('id_anho')
            ->union($query1)
            ->union($query2)
            ->orderBy('id_anho', 'desc')
            ->get();
        return $query;
    }
    public static function getMileageType($id_entidad)
    {
        $data = DB::table('plla_tipo_kilometraje as a')
            ->where('a.id_entidad', $id_entidad)
            ->where('a.vigencia', 1)
            ->select('a.id_tipo_kilometraje', 'a.nombre', 'a.nombrecorto', 'a.importe')
            ->orderBy('a.id_tipo_kilometraje', 'asc')
            ->get();
        return $data;
    }


    public static function getDays($id_tipo_horario)
    {
        $data = DB::table('plla_dias')
            ->where('vigencia', 1)
            ->whereraw("id_dia not in (select hd.id_dias from plla_horario_detalle hd where hd.id_tipo_horario = " . $id_tipo_horario . " )")
            ->select('id_dia', 'nombre', 'vigencia')
            ->orderBy('id_dia', 'asc')
            ->get();
        return $data;
    }

    public static function getTypeShedule($id_entidad, $id_depto)
    {
        $data = DB::table('plla_tipo_horario as a')
            ->where('a.vigencia', 1)
            ->where('a.id_entidad',  $id_entidad)
            ->where('a.id_depto', $id_depto)
            ->select('a.id_tipo_horario', 'a.nombre')
            ->orderBy('a.nombre', 'asc')
            ->get();
        return $data;
    }

    public static function getYearAssistance($id_entidad)
    {
        $query1 = DB::table(DB::raw('dual'))
            ->select(DB::raw("to_char(sysdate, 'yyyy') as id_anho"));
        $query2 = DB::table(DB::raw('dual'))
            ->select(DB::raw("to_char(case when to_char(sysdate, 'MM')='12' then  ADD_MONTHS(sysdate,4) else sysdate end ,'yyyy') as id_anho"));
        $query = DB::table('plla_proc_planilla')
            ->where('id_entidad', $id_entidad)
            ->select(DB::raw("to_char(id_anho) as id_anho"))
            ->groupBy('id_anho')
            ->union($query1)
            ->union($query2)
            ->orderBy('id_anho', 'desc')
            ->get();
        return $query;
    }
    public static function searchConceptPlla($search)
    {
        $sql = DB::table('plla_concepto_planilla')
            ->whereraw(ComunData::fnBuscar('nombre') . ' like ' . ComunData::fnBuscar("'%" . $search . "%'"))
            ->select('id_concepto_planilla', 'nombre', 'codigo')
            ->orderBy('nombre', 'asc')
            ->get();
        return $sql;
    }
    public static function getEstadoPlanilla()
    {
        $data = DB::table('plla_estado_planilla as a')
            ->where('a.vigencia', 1)
            ->select('a.id_estado_planilla', 'a.nombre')
            ->orderBy('a.id_estado_planilla', 'asc')
            ->get();
        return $data;
    }
    public static function getPlanillaEntidad($id_entidad)
    {
        $data = DB::table('plla_planilla_entidad as a')
            ->join('plla_grupo_planilla as b', 'a.id_grupo_planilla', '=', 'b.id_grupo_planilla')
            ->join('plla_tipo_planilla as c', 'a.id_tipo_planilla', '=', 'c.id_tipo_planilla')
            ->where('a.vigencia', 1)
            ->where('a.id_entidad', $id_entidad)
            ->select('a.id_planilla_entidad', 'b.nombre as nombre_grupo', 'c.nombre as nombre_tipo', 'a.id_tipo_planilla')
            ->orderBy('a.id_planilla_entidad', 'asc')
            ->get();
        return $data;
    }

    public static function getTypePeriodBS($tipo)
    {
        $data = DB::table('plla_tipo_periodo_bs as a')
            ->where('a.vigencia', 1)
            ->where('a.tipo', $tipo)
            ->select('a.id_tipo_periodo_bs', 'a.tipo', 'a.nombre')
            ->orderBy('a.tipo', 'desc')
            ->get();
        return $data;
    }

    public static function searchTrabajadorHolidays($datos)
    {
        $data = DB::table('moises.VW_TRABAJADOR as a')
            ->join('moises.condicion_laboral e', 'a.id_condicion_laboral', '=', 'e.id_condicion_laboral')
            ->whereraw("(upper(a.nombre) like upper('%" . $datos . "%')
            or upper(a.nombre ||' ' || a.paterno ) like upper('%" . $datos . "%')
            or upper(a.paterno ||' ' || a.materno ) like upper('%" . $datos . "%')
            or a.num_documento like '%" . $datos . "%')")
            ->select(
                DB::raw("(a.nombre|| ' ' || a.paterno|| ' ' || a.materno) as nombres"),
                'a.id_persona',
                'a.num_documento',
                DB::raw("to_char(a.fecha_inicio, 'YYYY-MM-DD') as fecha_inicio"),
                DB::raw("to_char(a.fecha_fin_previsto, 'YYYY-MM-DD') as fecha_fin_previsto"),
                DB::raw("to_char(a.fecha_fin_efectivo, 'YYYY-MM-DD') as fecha_fin_efectivo"),
                'e.nombre as nombre_condicion'
            )
            ->orderBy('a.nombre')
            ->get();
        return $data;
    }
    public static function listMyPeriodosAsig($id_persona)
    {
        $data = DB::table('eliseo.plla_periodo_vac_trab as a')
            ->join('eliseo.plla_periodo_vac as b', 'a.ID_PERIODO_VAC', '=', 'b.ID_PERIODO_VAC')
            ->join('eliseo.plla_estado_vac_trab as c', 'a.id_estado_vac_trab', '=', 'c.id_estado_vac_trab')
            ->where('a.id_persona', $id_persona)
            ->select(
                'b.nombre',
                'a.periodo_ini',
                'a.periodo_fin',
                'c.nombre as estado',
                'a.id_periodo_vac',
                'b.anho_inicio'
            )
            ->orderBy('b.anho_inicio', 'desc')
            ->take(1) //obtener  el un solo registro 'b.anho_inicio'
            ->get();
        return $data;
    }
    public static function getPeriodosSinAsig($id_entidad, $id_persona)
    {
        $idNoAdelantadas = ['01', '03'];
        $ids = DB::table('eliseo.plla_periodo_vac_trab as a')
            ->where('a.id_persona', $id_persona)
            ->select('a.id_periodo_vac')
            ->pluck('a.id_periodo_vac');
        $id_periodo_vac = $ids;
        $query = DB::table('eliseo.plla_periodo_vac a')
            ->where('a.id_entidad', $id_entidad)
            ->whereNotIn('a.id_periodo_vac', $id_periodo_vac)
            ->whereNotIn('a.id_estado_periodo_vac', $idNoAdelantadas)
            ->select('a.id_periodo_vac', 'a.nombre', 'a.min_dias_periodo1', 'a.min_dias_periodo2', 'a.anho_inicio', 'a.id_estado_periodo_vac')
            ->orderBy('a.anho_inicio', 'desc')
            ->get();
        return $query;
    }

    public static function showTrabajadorHolidays($id_entidad, $id_depto, $id_periodo_vac, $id_persona)
    {
        $q = DB::table('moises.vw_trabajador a');
        $q->join('eliseo.plla_periodo_vac_trab x', 'a.id_persona', '=', 'x.id_persona'); //agrgado recientemente
        $q->join('moises.persona_natural z', 'z.id_persona', '=', 'a.id_persona'); //agrgado recientemente 02/09/2020
        $q->join('org_sede_area b', 'a.id_sedearea', '=', 'b.id_sedearea');
        $q->join('moises.tipo_documento c', 'a.id_tipodocumento', '=', 'c.id_tipodocumento');
        $q->join('moises.situacion_trabajador d', 'a.id_situacion_trabajador', '=', 'd.id_situacion_trabajador');
        $q->join('moises.condicion_laboral e', 'a.id_condicion_laboral', '=', 'e.id_condicion_laboral');
        $q->join('moises.tipo_tiempo_trabajo f', 'a.id_tipo_tiempo_trabajo', '=', 'f.id_tipo_tiempo_trabajo');
        $q->join('org_area g', 'b.id_area', '=', 'g.id_area');
        $q->leftjoin('plla_tipo_horario th', 'th.id_tipo_horario', '=', 'a.id_tipo_horario');
        $q->leftjoin('moises.tipo_control_personal tc', 'tc.id_tipo_control_personal', '=', 'a.id_tipo_control_personal');
        $q->leftJoin('eliseo.plla_puesto pue', 'a.id_puesto', '=', 'pue.id_puesto');
        $q->where('b.id_entidad', $id_entidad);
        $q->whereraw("b.id_depto like '" . $id_depto . "%'");
        $q->where('x.id_periodo_vac', $id_periodo_vac);
        $q->where('x.id_persona', $id_persona);
        $q->select(
            'a.id_persona',
            DB::raw("(a.nombre || ' ' || a.paterno || ' ' || a.materno) as nombres"),
            'g.nombre as nombre_area',
            'a.num_documento',
            'a.fecha_inicio',
            'a.fecha_fin_previsto',
            'a.fecha_fin_efectivo',
            'd.nombre as nombre_corto',
            'e.nombre as nombre_condicion',
            'pue.nombre as nombre_puesto',
            'a.correo',
            'a.id_sedearea',
            'z.foto' //agrgado recientemente 02/09/2020
        );
        $q->orderBy('a.nombre', 'asc');
        $data = $q->first();
        //  ->get();
        return $data;
    }
    public static function searchsTrabajadorAprobe($request)
    {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_area = $request->id_area;
        $searchs = $request->searchs;
        $query = DB::table('moises.vw_trabajador as a')
            ->join('eliseo.org_sede_area as c', 'a.id_sedearea', '=', 'c.id_sedearea')
            ->where('c.id_entidad',  $id_entidad)
            ->whereraw("c.id_depto like '" . $id_depto . "%'")
            ->where('c.id_area', $id_area)
            ->whereraw("(upper(a.nombre) like upper('%" . $searchs . "%')
                                or upper(a.nombre ||' ' || a.paterno ) like upper('%" . $searchs . "%')
                                or upper(a.paterno ||' ' || a.materno ) like upper('%" . $searchs . "%')
                                or a.num_documento like '%" . $searchs . "%')")
            ->select('a.id_persona', DB::raw("(a.nombre|| ' ' ||a.paterno|| ' ' ||a.materno) nombres"), 'a.num_documento')
            ->get();
        return $query;
    }
    public static function trabajadoresPuesto($request)
    {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_sedearea = $request->id_sedearea;
        $id_puesto = $request->id_puesto;
        $query = DB::table('moises.vw_trabajador as a')
            ->join('eliseo.org_sede_area as c', 'a.id_sedearea', '=', 'c.id_sedearea')
            ->where('c.id_entidad',  $id_entidad)
            ->whereraw("c.id_depto like '" . $id_depto . "%'")
            ->where('a.id_sedearea', $id_sedearea)
            ->where('a.id_puesto', $id_puesto)
            ->select('a.id_persona', DB::raw("(a.nombre|| ' ' ||a.paterno|| ' ' ||a.materno) nombres"), 'a.num_documento')
            ->orderBy('a.nombre')
            ->get();
        return $query;
    }

    public static function getFirmaTrabajador($id_persona)
    {
        $url_dw = ComunData::ruta_url(url('gth'));
        $items = DB::table('moises.persona_natural as a')
            ->where('a.id_persona', $id_persona)
            ->select(
                'a.id_persona',
                'a.firma as nombre_firma',
                DB::raw("('" . $url_dw . "/'||a.firma) as urls_dw"),
                DB::raw("substr(a.documento_url,instr(a.documento_url, '.', -1) +1,length(a.documento_url)) as formato")
            )
            ->first();
        if (file_exists('gth/' . $items->nombre_firma)) {
            $items->urls_dw  = $items->urls_dw;
        } else {
            $items->urls_dw  = '';
        }
        $object['id_persona'] = $items->id_persona;
        $object['nombre_firma'] = $items->nombre_firma;
        $object['urls_dw'] = $items->urls_dw;
        $object['formato'] = $items->formato;

        return $object;
    }

    public static function getMotivoRequerimiento()
    {
        $data = DB::table('plla_motivo_req')
            ->where('vigencia', 1)
            ->select('id_motivo_req', 'nombre', 'nombrecorto', 'orden')
            ->orderBy('orden', 'asc')
            ->get();
        return $data;
    }

    public static function getModalidadSeleccion()
    {
        $data = DB::table('plla_modalidad_req')
            ->where('vigencia', 1)
            ->select('id_modalidad_req', 'nombre', 'nombrecorto', 'codigo', 'orden')
            ->orderBy('orden', 'asc')
            ->get();
        return $data;
    }

    public static function getCodigoAdmision()
    {
        $data = DB::table('plla_codigo_admision')
            ->where('vigencia', 1)
            ->select('id_codigo_admision', 'nombre', 'nombrecorto', 'codigo', 'codaps')
            ->get();
        return $data;
    }

    public static function getTipoTiempoTrabajo()
    {
        $data = DB::table('moises.tipo_tiempo_trabajo')
            ->where('vigencia', 1)
            ->select('id_tipo_tiempo_trabajo', 'nombre', 'hora_max', 'tiempo')
            ->get();
        return $data;
    }

    public static function getTipoContrato()
    {
        $data = DB::table('eliseo.plla_tipo_contrato')
            ->where('vigencia', 1)
            ->select('id_tipo_contrato', 'nombre', 'nombre_corto')
            ->get();
        return $data;
    }

    public static function getEstadoReq()
    {
        $data = DB::table('plla_estado_req')
            ->where('vigencia', 1)
            ->select('id_estado_req', 'nombre', 'nombrecorto', 'orden')
            ->orderBy('orden', 'asc')
            ->get();
        return $data;
    }

    public static function getCondicionLaboral()
    {
        $data = DB::table('moises.condicion_laboral')
            ->where('vigencia', 1)
            ->select('id_condicion_laboral', 'nombre', 'lim_h_semanales', 'restringir_h_semanales')
            ->get();
        return $data;
    }

    public static function getPerfilPuestobyArea($id_sedearea)
    {
        $data = DB::table('plla_perfil_puesto p')
            ->join('plla_puesto pu', 'pu.id_puesto', '=', 'p.id_puesto')
            ->where('pu.vigencia', 1)
            ->where('p.id_sedearea', $id_sedearea)
            ->select(
                'p.id_perfil_puesto',
                'pu.nombre',
                'pu.id_puesto',
                'p.id_situacion_educativo',
                'p.id_tipo_horario'
            )
            ->get();
        return $data;
    }

    public static function getSedes()
    {
        $data = DB::table('org_sede')
            ->select('id_sede', 'nombre', 'codigo')
            ->get();
        return $data;
    }

    public static function getAreaRequest($searchs, $id_entidad, $id_depto)
    {
        $sql = DB::table('org_sede_area s');
        $sql->join('org_area a', 'a.id_area', '=', 's.id_area');
        $sql->join('conta_entidad_depto c', 'c.id_depto', '=', 's.id_depto');
        $sql->where('s.id_entidad', '=', $id_entidad);
        $sql->whereraw("s.id_depto like '" . $id_depto . "%'");
        $sql->whereraw(ComunData::fnBuscar('a.nombre') . ' like ' . ComunData::fnBuscar("'%" . $searchs . "%'"));
        $sql->select(
            's.id_sedearea',
            'a.id_area',
            'c.nombre as depto',
            'a.nombre as area',
            'c.id_depto'
        );
        $data = $sql->get();
        return $data;
    }

    public static function getAllTypeShedule()
    {
        $data = DB::table('plla_tipo_horario as a')
            ->select('a.id_tipo_horario', 'a.nombre')
            ->orderBy('a.nombre', 'asc')
            ->get();
        return $data;
    }

    public static function getCCosto($request)
    {
        /*$data = DB::table('conta_entidad_depto')
            ->where('id_entidad', '=', $request->id_entidad)
            ->where('id_depto', '=', $request->id_depto)
            ->select('*')
            ->get();
        return $data;*/
        $data = DB::table('org_sede_area oa')
            ->join('conta_entidad_depto cd', 'cd.id_depto', '=', 'oa.id_depto')
            ->where('oa.id_entidad', '=', $request->id_entidad)
            ->whereraw("oa.id_depto like '" . $request->id_depto . "%'")
            ->where('oa.id_area', '=', $request->id_area)
            ->select('cd.nombre', 'oa.id_sedearea')
            ->get();
        return $data;
    }

    public static function getNivelModalidad($request)
    {
        $data = DB::table('DAVID.ACAD_PROGRAMA_ESTUDIO a')
            ->join('DAVID.TIPO_NIVEL_ENSENANZA b', 'b.ID_NIVEL_ENSENANZA', '=', 'a.ID_NIVEL_ENSENANZA')
            ->join('DAVID.TIPO_MODALIDAD_ESTUDIO c', 'c.ID_MODALIDAD_ESTUDIO', '=', 'a.ID_MODALIDAD_ESTUDIO')
            ->join('ORG_SEDE_AREA s', 's.ID_SEDEAREA', '=', 'a.ID_SEDEAREA')
            ->where('s.id_entidad', '=', $request->id_entidad)
            ->whereraw("s.id_depto like '" . $request->id_depto . "%'")
            ->orderBy('b.NOMBRE')
            ->groupBy('a.ID_NIVEL_ENSENANZA', 'b.NOMBRE', 'a.ID_MODALIDAD_ESTUDIO', 'c.NOMBRE')
            ->select(
                'a.ID_NIVEL_ENSENANZA',
                'b.NOMBRE as NIVEL_ENSENANZA',
                'a.ID_MODALIDAD_ESTUDIO',
                'c.NOMBRE as MODALIDAD_ESTUDIO'
            )
            ->get();
        return $data;
    }

    public static function getNivelEnsenanza($request)
    {
        $data = DB::table('DAVID.ACAD_PROGRAMA_ESTUDIO a')
            ->join('DAVID.TIPO_NIVEL_ENSENANZA b', 'b.ID_NIVEL_ENSENANZA', '=', 'a.ID_NIVEL_ENSENANZA')
            ->join('ORG_SEDE_AREA s', 's.ID_SEDEAREA', '=', 'a.ID_SEDEAREA')
            ->where('s.id_entidad', '=', $request->id_entidad)
            ->whereraw("s.id_depto like '" . $request->id_depto . "%'")
            ->groupBy('b.ID_NIVEL_ENSENANZA', 'b.NOMBRE')
            ->select(
                'b.ID_NIVEL_ENSENANZA',
                'b.NOMBRE'
            )
            ->get();
        return $data;
    }

    public static function getModalidad($request)
    {
        $data = DB::table('DAVID.ACAD_PROGRAMA_ESTUDIO a')
            ->join('DAVID.TIPO_MODALIDAD_ESTUDIO c', 'c.ID_MODALIDAD_ESTUDIO', '=', 'a.ID_MODALIDAD_ESTUDIO')
            ->join('ORG_SEDE_AREA s', 's.ID_SEDEAREA', '=', 'a.ID_SEDEAREA')
            ->where('s.id_entidad', '=', $request->id_entidad)
            ->whereraw("s.id_depto like '" . $request->id_depto . "%'")
            ->groupBy('c.ID_MODALIDAD_ESTUDIO', 'c.NOMBRE')
            ->select(
                'c.ID_MODALIDAD_ESTUDIO',
                'c.NOMBRE'
            )
            ->get();
        return $data;
    }

    public static function getInfoPersonaCBH($request)
    {
        $id_persona = $request->id_persona;
        $query =    "select p.nombre,p.paterno,p.materno,td.siglas,pn.num_documento,cl.nombre as condicion, a.nombre as area,se.nombre grado,
        tc.nombre tipo_contrato, pu.nombre puesto,sa.id_entidad,sa.id_depto, jl.nombre as jornada,pnt.fecha_inicio as inicio,
        pnt.fecha_fin_previsto as fin
        from moises.persona p
        join moises.persona_natural pn on pn.id_persona=p.id_persona
        join moises.persona_natural_trabajador pnt on pnt.id_persona=pn.id_persona
        left join moises.condicion_laboral cl on cl.id_condicion_laboral= pnt.id_condicion_laboral
        join moises.tipo_documento td on td.id_tipodocumento = pn.id_tipodocumento
        join org_sede_area sa on sa.id_sedearea = pnt.id_sedearea
        join org_area a on a.id_area=sa.id_area
        left join MOISES.situacion_educativa se on se.id_situacion_educativo = pn.id_situacion_educativo 
        left join plla_puesto pu on pu.id_puesto = pnt.id_puesto
        left join tipo_contrato tc on tc.id_tipocontrato = pnt.id_tipo_contrato
        left join MOISES.tipo_tiempo_trabajo jl on jl.id_tipo_tiempo_trabajo=pnt.id_tipo_tiempo_trabajo
        where p.id_persona=" . $id_persona;
        $persona = collect(DB::select($query))->first();
        //$persona->info = PersonData::showPersonNatural($id_persona, url(''))[0];
        $persona->educative = EmployeeData::getInformationAcademic($id_persona, url(''));
        return $persona;
    }

    public static function getAreaParents($request)
    {
        $data = DB::table('eliseo.vw_area')
            ->where("id_entidad", "=", $request->id_entidad)
            ->whereRaw(" (ID_PARENT IS NULL  OR TIENEHIJO > 0 ) ")
            ->select('*')
            ->get();
        return $data;
    }

    public static function getTipoRegimenLaboral()
    {
        $data = DB::table('moises.tipo_regimen_laboral')
            ->where('vigencia', 1)
            ->select('id_tipo_regimen_laboral', 'nombre')
            ->get();
        return $data;
    }

    public static function getTipoSCTRPension()
    {
        $data = DB::table('moises.tipo_sctr_pension')
            ->where('vigencia', 1)
            ->select('id_tipo_sctr_pension', 'nombre')
            ->get();
        return $data;
    }

    public static function getSituacionTrabajador()
    {
        $data = DB::table('moises.situacion_trabajador')
            ->where('vigencia', 1)
            ->select('id_situacion_trabajador', 'nombre')
            ->get();
        return $data;
    }

    public static function getTipoPago()
    {
        $data = DB::table('eliseo.plla_tipo_pago')
            ->where('vigencia', 1)
            ->select('id_tipo_pago', 'nombre')
            ->get();
        return $data;
    }

    public static function getTipoCategoriaOcupa()
    {
        $data = DB::table('eliseo.plla_tipo_categ_ocupa')
            //->where('vigencia', 1)
            ->select('id_tipo_categ_ocupa', 'nombre', 'vigencia')
            ->get();
        return $data;
    }

    public static function getSituacionEspecial()
    {
        $data = DB::table('moises.situacion_especial')
            ->where('vigencia', 1)
            ->select('id_situacion_especial', 'nombre', 'nombrecorto')
            ->get();
        return $data;
    }

    public static function getTipoDobleTrib()
    {
        $data = DB::table('eliseo.plla_tipo_doble_trib')
            ->where('vigencia', 1)
            ->select('id_tipo_doble_trib', 'nombre')
            ->get();
        return $data;
    }

    public static function getPerRemuneracion()
    {
        $sql =  DB::table('eliseo.plla_periodo_remu ppr')
            ->where('ppr.vigencia', '1')
            ->select('ppr.id_periodo_remu', 'ppr.nombre')
            ->orderBy('ppr.nombre', 'asc')
            ->get();
        return $sql;
    }

    public static function searchOcupacion($searchs)
    {
        $sql = DB::table('moises.tipo_ocupacion');
        $sql->whereraw(ComunData::fnBuscar('nombre') . ' like ' . ComunData::fnBuscar("'%" . $searchs . "%'"));
        $sql->orderBy('nombre', 'asc');
        $sql->select('*');
        $data = $sql->paginate(20);
        return $data;
    }
    public static function getTipoEstadoVacTrab()
    {
        $data = DB::table('eliseo.plla_estado_vac_trab')
            ->where('vigencia', 1)
            ->select('id_estado_vac_trab', 'nombre', 'nombrecorto')
            ->orderBy('orden')
            ->get();
        return $data;
    }
    public static function getEstadoSolicitud()
    {
        $data = DB::table('eliseo.plla_estado_vac_adel')
            ->where('vigencia', 1)
            ->select('id_estado_vac_adel', 'nombre', 'nombrecorto')
            ->orderBy('orden')
            ->get();
        return $data;
    }
    public static function getPeriodoAprobe($request)
    {
        $id_entidad = $request->id_entidad;
        $id_persona = $request->id_persona;
        $data = DB::table('eliseo.plla_periodo_vac')
            ->where('id_entidad', 7124)
            ->whereraw("ID_PERIODO_VAC NOT IN(
                SELECT A.ID_PERIODO_VAC FROM PLLA_PERIODO_VAC_TRAB A, PLLA_ROL_VACACIONAL V
                WHERE A.ID_PERIODO_VAC_TRAB=V.ID_PERIODO_VAC_TRAB
                AND A.ID_PERSONA=$id_persona
                AND coalesce(A.TOTAL_DIAS,0)=30)
                AND ID_ESTADO_PERIODO_VAC not in('02','03')")
            ->select(
                'id_periodo_vac',
                'id_entidad',
                'nombre',
                'comentario',
                'min_dias_periodo1',
                'min_dias_periodo2',
                'id_estado_periodo_vac',
                'anho_inicio'
            )
            ->get();
        return $data;
    }

    public static function getTipoStatus()
    {
        $data = DB::table('moises.tipo_status')
            ->where('vigencia', 1)
            ->select('id_tipo_status', 'nombre', 'codigo')
            ->get();
        return $data;
    }
    public static function getGrupoPlanilla()
    {
        $data = DB::table('PLLA_GRUPO_PLANILLA')
            ->where('vigencia', 1)
            ->select('id_grupo_planilla', 'nombre', 'codigo', 'nombrecorto')
            ->orderBy('orden')
            ->get();
        return $data;
    }


    public static function listMyEntityAccess($id_user, $id_entidad, $request)
    {
        $restringido = 'N';
        $idNivelAcceso = $request->id_acceso_nivel;

        if ($request->has('restringido')) {
            $restringido = $request->restringido;
        }
        $q = DB::table('conta_entidad as a');
        $q->join('conta_entidad_usuario as b', 'a.id_entidad', '=', 'b.id_entidad');
        $q->where('b.id_persona', '=', $id_user);
        $q->whereraw('b.estado in (0,1)');
        if ($restringido == 'S') {
            $q->whereraw(" a.id_entidad in(select x.id_entidad from vw_plla_acceso_nivel x where x.id_acceso_nivel=" . $idNivelAcceso . ")");
        }
        if ($restringido == 'U') {
            $q->where('a.id_entidad', $id_entidad);
        }
        $q->select(
            'a.id_entidad',
            'a.nombre',
            'b.id_persona',
            'b.estado',
            DB::raw("CASE WHEN a.id_entidad = " . $id_entidad . " THEN 1 ELSE 0 END as selection")
        );
        $q->orderBy('a.nombre', 'asc');
        $oQuery =  $q->get();

        return $oQuery;
    }
    public static function listMyDeptoAccess($request, $id_depto)
    {
        $restringido = 'N';
        $idNivelAcceso = $request->id_acceso_nivel;

        if ($request->has('restringido')) {
            $restringido = $request->restringido;
        }
        $id_entidad = $request->id_entidad;

        $objet = DB::table('eliseo.lamb_acceso_nivel')
            ->where('id_acceso_nivel', $idNivelAcceso)
            ->select('id_tipo_nivel_vista')
            ->first();

        $q = DB::table('conta_entidad_depto');
        $q->where('id_entidad', '=', $id_entidad);
        $q->whereraw(DB::raw("es_grupo = 1"));
        $q->whereraw(DB::raw("length(id_depto) = 1"));
        if ($restringido == 'S') {
            if ($objet->id_tipo_nivel_vista >= 3) {
                $q->whereraw("id_depto in(select x.id_depto from vw_plla_acceso_nivel x where x.id_acceso_nivel=" . $idNivelAcceso . ")");
            }
        }
        if ($restringido == 'U') {
            $q->where('id_depto', $id_depto);
        }
        $q->select(
            'id_depto',
            'nombre',
            DB::raw("CASE WHEN id_depto = '" . $id_depto . "' THEN 1 ELSE 0 END as selection")
        );
        $q->orderBy('id_depto', 'asc');
        $Query = $q->get();

        return $Query;
    }
    public static function searchMyListAreasAccess($request, $id_persona)
    {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $department = $request->department;
        $restringido = 'N';
        $idNivelAcceso = $request->id_acceso_nivel;
        $tipo = 'A'; // A = Autocompletar: segun el tipo devuelve una lista o un buscador

        if ($request->has('tipo')) {
            $tipo = $request->tipo;
        }

        $objet = DB::table('eliseo.lamb_acceso_nivel')
            ->where('id_acceso_nivel', $idNivelAcceso)
            ->select('id_tipo_nivel_vista')
            ->first();


        if ($request->has('restringido')) {
            $restringido = $request->restringido;
        }
        if ($restringido == 'S') {
            $ids = ParameterData::areaNivels($idNivelAcceso);
            //    dd($ids);
        }
        $q = DB::table('org_area as a');
        if ($tipo == 'A') {
            $q->whereraw(DB::raw("(" . ComunData::fnBuscar('a.nombre') . ' like ' . ComunData::fnBuscar("'%" . $department . "%'") . ")"));
        }
        $q->whereraw("a.id_area in(
            select b.id_area from org_sede_area b
            where b.id_entidad=" . $id_entidad . "
            and b.id_depto like '" . $id_depto . "%'
          )");
        if ($restringido == 'S') {
            if ($objet->id_tipo_nivel_vista == 4) {
                $q->whereIn('a.id_area', $ids);
            }
            if ($objet->id_tipo_nivel_vista == 5) {
                $q->whereraw("a.id_area in(select id_area from vw_plla_trabajador_acceso where id_persona=" . $id_persona . ")");
            }
        }
        if ($restringido == 'U') {
            $q->whereraw("a.id_area in(select id_area from vw_plla_trabajador_acceso where id_persona=" . $id_persona . ")");
        }
        $q->select('a.id_area', 'a.nombre');
        if ($tipo == 'A') { // A = Autocompletar
            $q->offset(0);
            $q->limit(20);
        }
        $query = $q->get();
        return $query;
        // $q = DB::table('org_area as a');
        //     $q->join('org_sede_area b', 'b.id_area', '=', 'a.id_area');
        //     $q->join('conta_entidad_depto c', 'c.id_depto', '=', DB::raw('b.id_depto and b.id_entidad = c.id_entidad'));
        //     $q->where('b.id_entidad', $id_entidad);
        //     $q->whereraw(DB::raw("b.id_depto like '" . $id_depto . "%'"));
        //     $q->whereraw(DB::raw("(c.id_depto LIKE '%" . $department . "%' OR " . ComunData::fnBuscar('a.nombre') . ' like ' . ComunData::fnBuscar("'%" . $department . "%'") . " or c.nombre like " . ComunData::fnBuscar("'%" . $department . "%'") . ")"));
        //     if ($restringido == 'S') {
        //         if ($objet->id_tipo_nivel_vista == 4) {
        //             $q->whereIn('a.id_area', $ids);
        //         }
        //         if ($objet->id_tipo_nivel_vista == 5) {
        //             $q->whereraw("a.id_area in(select id_area from vw_plla_trabajador_acceso where id_persona=".$id_persona.")"); 
        //         }
        //     } 
        //     if ($restringido == 'U') {
        //         $q->whereraw("a.id_area in(select id_area from vw_plla_trabajador_acceso where id_persona=".$id_persona.")");  
        //     }
        //     $q->select(
        //         'a.id_area',
        //         'b.id_sedearea',
        //         'a.nombre',
        //         'b.id_entidad',
        //         'b.id_depto',
        //         'c.nombre as depto',
        //         'b.id_sede',
        //         DB::raw("a.nombre||' - '||c.id_depto||'-'||c.nombre as nom_comp")
        //     );
        //     $q->offset(0);
        //     $q->limit(20);
        //    $query = $q->get();
        // return $query;
    }
    public static function areaNivels($idNivelAcceso)
    {
        $ids = array();

        $objet = DB::table('eliseo.lamb_acceso_nivel')
            ->where('id_acceso_nivel', $idNivelAcceso)
            ->select('id_tipo_nivel_vista')
            ->first();

        if ($objet->id_tipo_nivel_vista == 4) {
            $list =  DB::table('eliseo.vw_plla_acceso_nivel')
                ->where('id_acceso_nivel', $idNivelAcceso)
                ->select('id_area', 'id_tipo_nivel_area', 'nivelhijo')
                ->get();

            foreach ($list as $datos) {
                $nivelHijo = $datos->nivelhijo;
                $where = "";
                if ($nivelHijo > 0) {
                    $hijos = array();
                    for ($j = 1; $j <= $nivelHijo; $j++) {
                        $hijos[] = $j;
                    }
                    $where = " AND LEVEL IN(" . implode(",", $hijos) . ") ";
                }
                $que = "SELECT ID_AREA
                FROM ORG_AREA
                 START WITH ID_AREA = " . $datos->id_area . "
                CONNECT BY PRIOR ID_AREA = ID_PARENT
                " . $where . "
                ORDER SIBLINGS BY NOMBRE";
                $dta = DB::select($que);

                foreach ($dta as $dtaa) {
                    array_push($ids, $dtaa->id_area);
                }
            }
        }
        return $ids;
    }
    public static function getAccesoNivel($request, $id_user)
    {
        $codigo = $request->codigo_acceso;
        $obje = DB::table('eliseo.lamb_modulo')
            ->where('codigo', $codigo)
            ->select('id_modulo', 'accesoxnivel')
            ->first();

        $ob = DB::table('enoc.vw_plla_acceso_nivel')
            ->where('usuario', 'S')
            ->where('id_persona', $id_user)
            ->where('id_modulo', '=', $obje->id_modulo)
            ->select('*')
            ->first();

        $items['modulo'] = $obje;
        $items['acceso_nivel'] = $ob;

        return $items;
    }
    public static function listArbolProfilePosition($request)
    {

        $id_area = $request->id_area;
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $q = DB::table('plla_perfil_puesto as a');
        $q->join('org_sede_area as b', 'a.id_sedearea', '=', 'b.id_sedearea');
        $q->join('plla_puesto as d', 'a.id_puesto', '=', 'd.id_puesto');
        $q->where("b.id_area", $id_area);
        $q->where("b.id_entidad", $id_entidad);
        $q->whereraw("b.id_depto like '" . $id_depto . "%'");
        $q->where("a.nivel", 0);
        $q->select(
            'a.id_perfil_puesto',
            'a.id_puesto',
            'a.id_sedearea',
            'b.id_depto',
            'd.nombre',
            DB::raw("(select count(*) from moises.persona_natural_trabajador x where x.id_sedearea=a.id_sedearea and x.id_puesto=a.id_puesto) as cantidad_trab_actual")
        );
        $q->orderBy('a.id_perfil_puesto', 'asc');
        $data = $q->get();

        $datos = array();

        foreach ($data as $row) {
            $items = array();
            $items['id_perfil_puesto'] = $row->id_perfil_puesto;
            $items['id_sedearea'] = $row->id_sedearea;
            $items['width'] = '200px';
            $items['nivel'] = '1';
            $items['nombre'] = $row->nombre;
            $items['id_puesto'] = $row->id_puesto;
            $items['cantidad_trab_actual'] = $row->cantidad_trab_actual;
            $items['children'] = ParameterData::ChildrenlistArbolProfilePosition(1, $id_area, $id_entidad, $id_depto, $row->id_perfil_puesto);
            $datos[] = $items;
        }


        return $datos;
    }
    private static function ChildrenlistArbolProfilePosition($nivel, $id_area, $id_entidad, $id_depto, $id_perfil_puesto)
    {
        $q = DB::table('plla_perfil_puesto as a');
        $q->join('org_sede_area as b', 'a.id_sedearea', '=', 'b.id_sedearea');
        $q->join('plla_puesto as d', 'a.id_puesto', '=', 'd.id_puesto');
        $q->where("b.id_area", $id_area);
        $q->where("b.id_entidad", $id_entidad);
        $q->whereraw("b.id_depto like '" . $id_depto . "%'");
        $q->where("a.id_perfil_puesto_jefe", $id_perfil_puesto);
        $q->select(
            'a.id_perfil_puesto',
            'a.id_puesto',
            'a.id_sedearea',
            'b.id_depto',
            'd.nombre',
            DB::raw("(select count(*) from moises.persona_natural_trabajador x where x.id_sedearea=a.id_sedearea and x.id_puesto=a.id_puesto) as cantidad_trab_actual")
        );
        $q->orderBy('a.id_perfil_puesto', 'asc');
        $data = $q->get();

        $datos = array();
        $nivel++;
        foreach ($data as $row) {
            $items = array();
            $items['id_perfil_puesto'] = $row->id_perfil_puesto;
            $items['id_sedearea'] = $row->id_sedearea;
            $items['width'] = '100px';
            $items['nivel'] = $nivel . "";
            $items['nombre'] = $row->nombre;
            $items['id_puesto'] = $row->id_puesto;
            $items['cantidad_trab_actual'] = $row->cantidad_trab_actual;
            $items['children'] = ParameterData::ChildrenlistArbolProfilePosition($nivel, $id_area, $id_entidad, $id_depto, $row->id_perfil_puesto);

            $datos[] = $items;
        }

        return $datos;
    }

    public static function searchPersonaAccess($request, $id_user)
    {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $datos = $request->datos;
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
            //    dd($ids);
        }

        $q = DB::table('moises.VW_TRABAJADOR as a');
        $q->join('eliseo.org_sede_area b', 'a.id_sedearea', '=', 'b.id_sedearea');
        $q->join('moises.condicion_laboral e', 'a.id_condicion_laboral', '=', 'e.id_condicion_laboral');

        $q->where("b.id_entidad", $id_entidad);
        $q->whereraw("b.id_depto like '" . $id_depto . "%'");
        if ($restringido == 'S') {
            if ($objet->id_tipo_nivel_vista == 4) {
                $q->whereIn('b.id_area', $ids);
            }
            if ($objet->id_tipo_nivel_vista == 5) {
                $q->where('a.id_persona', $id_user);
            }
        }
        if ($restringido == 'U') {
            $q->where('a.id_persona', $id_user);
        }
        $q->whereraw("(upper(a.nombre) like upper('%" . $datos . "%')
            or upper(a.nombre ||' ' || a.paterno ) like upper('%" . $datos . "%')
            or upper(a.paterno ||' ' || a.materno ) like upper('%" . $datos . "%')
            or a.num_documento like '%" . $datos . "%')");
        $q->select(
            DB::raw("(a.nombre|| ' ' || a.paterno|| ' ' || a.materno) as nombres"),
            'a.id_persona',
            'a.num_documento',
            DB::raw("to_char(a.fecha_inicio, 'YYYY-MM-DD') as fecha_inicio"),
            DB::raw("to_char(a.fecha_fin_previsto, 'YYYY-MM-DD') as fecha_fin_previsto"),
            DB::raw("to_char(a.fecha_fin_efectivo, 'YYYY-MM-DD') as fecha_fin_efectivo"),
            'e.nombre as nombre_condicion'
        );
        $q->orderBy('a.nombre');
        $q->offset(0);
        $q->limit(20);
        $query = $q->get();
        return $query;
    }
    public static function moduleFather($request)
    {
        $id_entidad = $request->id_entidad;
        $id_persona = $request->id_persona;
        $query = "SELECT
        NOMBRE,
        ID_MODULO,
        ID_PADRE,
        ORDEN,
        NIVEL,
        CODIGO
       FROM lamb_modulo
       where ID_MODULO IN(
           select a.id_modulo
           from lamb_rol_modulo a
           WHERE  a.id_rol in(
             select id_rol from lamb_usuario_rol where id_persona=" . $id_persona . " and id_entidad=" . $id_entidad . "
           ) group by a.id_modulo
       )
       and nivel='1'
       and id_modulo=310";
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function tipoNivelVista()
    {
        $query = DB::table('eliseo.tipo_nivel_vista')
            ->where('vigencia', 1)
            ->select('id_tipo_nivel_vista', 'nombre', 'orden')
            ->orderBy('orden')
            ->get();
        return $query;
    }
    public static function tipoNivelArea()
    {
        $query = DB::table('eliseo.tipo_nivel_area')
            ->where('vigencia', 1)
            ->select('id_tipo_nivel_area', 'nombre', 'nivelhijo', 'orden')
            ->orderBy('orden')
            ->get();
        return $query;
    }
    public static function entidadDeprtamentoAreaPersona($request)
    {
        $id_persona = $request->id_persona;
        $query = "SELECT e.id_entidad||'-'||e.nombre as entidad,
        d.nombre as departamento,
        c.nombre as area,
        b.id_entidad,
        substr(b.id_depto,1,1) as id_depto,
        b.id_area
        from moises.PERSONA_NATURAL_TRABAJADOR a ,
        org_sede_area b,
        org_area c,
        conta_entidad_depto d,
        conta_entidad e
        where a.id_sedearea=b.id_sedearea
        and b.id_area=c.id_area
        and substr(b.id_depto,1,1)=d.id_depto
        and b.id_entidad=e.id_entidad
        and a.id_persona=" . $id_persona . "";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function searchAreas($request)
    {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $name = $request->name;
        $q = DB::table('org_area as a');
        $q->whereraw(DB::raw("(" . ComunData::fnBuscar('a.nombre') . ' like ' . ComunData::fnBuscar("'%" . $name . "%'") . ")"));
        $q->whereraw("a.id_area in(
            select b.id_area from org_sede_area b
            where b.id_entidad=" . $id_entidad . "
            and b.id_depto like '" . $id_depto . "%'
          )");
        $q->select('a.id_area', 'a.nombre');
        $q->offset(0);
        $q->limit(20);
        $query = $q->get();
        return $query;
    }

    public static function getServicios()
    {
        $sql = DB::table('eliseo.servicio s');
        $sql->select('*');
        $data = $sql->get();
        return $data;
    }

    public static function getServicioByUser($id_user)
    {
        $sql = DB::table('eliseo.servicio_usuario u');
        $sql->join("eliseo.servicio s", "s.id_servicio", "=", "u.id_servicio");
        $sql->where("u.id_usuario", $id_user);
        $sql->select('s.id_servicio');
        $data = $sql->get();
        return $data;
    }

    public static function registroServiciosUsuario($request)
    {
        $id_user = $request->id_usuario;
        $info = $request->data;
        $data = json_decode($info, true);
        $success = DB::table('eliseo.servicio_usuario')->where("id_usuario", $id_user)->delete();

        if (count($data)) {
            for ($i = 0; $i < count($data); $i++) {
                $element = $data[$i];
                $success = DB::table('eliseo.servicio_usuario')
                    ->insert([
                        "id_usuario" => $id_user,
                        "id_servicio" => $element['id_servicio']
                    ]);
                //id_servicio
            }
        }

        return [
            "success" => $success,
            "data" => []
        ];
    }
}
