<?php
namespace App\Http\Data\HumanTalentMgt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\HumanTalentMgt\ComunData;
use PDO;

class HolidaysData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public static function addProceGeneratePerioVac($request)
    {
        // dd('hhh', $id_anho, $id_entidad);
        $nerror = 0;
        $msgerror = '';
        $item=1;
        for ($i = 1; $i <= 200; $i++) {
            $msgerror .= '0';
        }
        $id_persona = $request->id_persona;
        $id_periodo_vac = $request->id_periodo_vac;
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_HUMAN_TALENT_MGT.SP_GENERAR_PERIODO_VAC_TRAB(
                                :P_ID_PERSONA, :P_ID_PERIODO_VAC, :P_ITEM, :P_ERROR, :P_MSGERROR
                                     ); end;");
        $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PERIODO_VAC', $id_periodo_vac, PDO::PARAM_INT);
        $stmt->bindParam(':P_ITEM', $item, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute();
        $return = [
            'nerror' => $nerror,
            'msgerror' => $msgerror,
        ];
        return $return;
    }
    public static function getGeneratePerioVac($id_periodo_vac, $id_persona)
    {
        $query = DB::table('eliseo.plla_periodo_vac_trab a')
            ->join('eliseo.plla_estado_vac_trab b', 'a.id_estado_vac_trab', '=', 'b.id_estado_vac_trab')
            ->where('a.id_persona', $id_persona)
            ->where('a.id_periodo_vac', $id_periodo_vac)
            ->select('a.id_periodo_vac_trab', 'a.id_periodo_vac', 'a.id_persona', 'a.total_dias', 'a.total_dias_efect', 'b.nombre as estado', 'a.comentario',
             DB::raw("to_char(a.periodo_ini, 'YYYY-MM-DD') as periodo_ini, to_char(a.periodo_fin, 'YYYY-MM-DD') as periodo_fin"),
             DB::raw("ADD_MONTHS(to_char(a.periodo_ini,'YYYY-MM-DD'), -12) as fecha_ini_trabajado"),
             DB::raw("ADD_MONTHS(to_char(a.periodo_fin,'YYYY-MM-DD'), -12) as fecha_fin_trabajado"), 'a.id_estado_vac_trab')
            ->get()->shift();
        return $query;
    }
    public static function saveProgramaming($id_user, $request)
    {

        $nerror = 0;
        $msgerror = '';
        for ($i = 1; $i <= 200; $i++) {
            $msgerror .= '0';
        }
        $id_rol_vacacion = '-';
        $opc = 'I';
        $id_periodo_vac_trab = $request->id_periodo_vac_trab;
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_HUMAN_TALENT_MGT.SP_GENERAR_ROL_VACACIONAL(
                                :P_ID_ROL_VACACION, :P_ID_PERIODO_VAC_TRAB,:P_FECHA_INI,:P_FECHA_FIN, :P_OPC, :P_ID_USER_REG, :P_ERROR, :P_MSGERROR
                                     ); end;");
        $stmt->bindParam(':P_ID_ROL_VACACION', $id_rol_vacacion, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_PERIODO_VAC_TRAB', $id_periodo_vac_trab, PDO::PARAM_STR);
        $stmt->bindParam(':P_FECHA_INI', $fecha_ini, PDO::PARAM_STR);
        $stmt->bindParam(':P_FECHA_FIN', $fecha_fin, PDO::PARAM_STR);
        $stmt->bindParam(':P_OPC', $opc, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_USER_REG', $id_user, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute();
        $return = [
            'nerror' => $nerror,
            'message' => $msgerror,
        ];
        return $return;
    }
    public static function listProgramingVacation($id_periodo_vac_trab)
    {
        $query = DB::table('plla_rol_vacacional a')
            ->join('eliseo.plla_estado_rol_vac as b', 'a.ID_ESTADO_ROL_VAC', '=', 'b.ID_ESTADO_ROL_VAC')
            ->where('a.id_periodo_vac_trab', $id_periodo_vac_trab)
            ->select(
                'a.id_rol_vacacion',
                'a.id_periodo_vac_trab',
                DB::raw("to_char(a.fecha_ini, 'YYYY-MM-DD') as fecha_ini, to_char(a.fecha_fin, 'YYYY-MM-DD') as fecha_fin"),
                DB::raw("case when a.ID_PARENT is null then a.ID_ROL_VACACION else a.ID_PARENT end as orden,
                    case when a.ID_PARENT is null  then 0 else 1 end as padre"),
                'a.dias',
                'a.dias_efect',
                'a.condicion',
                'a.id_parent',
                'b.nombre as estado',
                'a.confirmacion_salida',
                'a.confirmacion_retorno',
                'a.motivo_repro',
                'a.id_tipo_rol_vac',
                DB::raw("(case when a.fecha_ini < CURRENT_DATE +1 then 1 else 0 end) as fecha_calculado")
            )
            ->orderBy('orden', 'asc')
            ->orderBy('a.fecha_ini', 'asc')
            ->get();
        return $query;
    }
    public static function deleteProgramingVacation($id_rol_vacacion)
    {
        $query=DB::table('eliseo.plla_rol_vacacional')
        ->where('id_rol_vacacion', $id_rol_vacacion)
        ->select('id_periodo_vac_trab')
        ->first();

        $nerror = 0;
        $msgerror = '';
        for ($i = 1; $i <= 200; $i++) {
            $msgerror .= '0';
        }

        $opc = 'D';
        $id_user = 0;
        $id_periodo_vac_trab = $query->id_periodo_vac_trab;
        $fecha_ini = null;
        $fecha_fin = null;
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_HUMAN_TALENT_MGT.SP_GENERAR_ROL_VACACIONAL(
                                    :P_ID_ROL_VACACION, :P_ID_PERIODO_VAC_TRAB,:P_FECHA_INI,:P_FECHA_FIN, :P_OPC, :P_ID_USER_REG,:P_ERROR, :P_MSGERROR
                                         ); end;");
        $stmt->bindParam(':P_ID_ROL_VACACION', $id_rol_vacacion, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_PERIODO_VAC_TRAB', $id_periodo_vac_trab, PDO::PARAM_STR);
        $stmt->bindParam(':P_FECHA_INI', $fecha_ini, PDO::PARAM_STR);
        $stmt->bindParam(':P_FECHA_FIN', $fecha_fin, PDO::PARAM_STR);
        $stmt->bindParam(':P_OPC', $opc, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_USER_REG', $id_user, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute();
        $return = [
            'nerror' => $nerror,
            'message' => $msgerror,
        ];
        return $return;
    }
    public static function updateProgramingVacation($id_user, $id_rol_vacacion, $request)
    {

        $nerror = 0;
        $msgerror = '';
        for ($i = 1; $i <= 200; $i++) {
            $msgerror .= '0';
        }

        $opc = 'U';
        $id_periodo_vac_trab = $request->id_periodo_vac_trab;
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_HUMAN_TALENT_MGT.SP_GENERAR_ROL_VACACIONAL(
                                    :P_ID_ROL_VACACION, :P_ID_PERIODO_VAC_TRAB,:P_FECHA_INI,:P_FECHA_FIN, :P_OPC, :P_ID_USER_REG,:P_ERROR, :P_MSGERROR
                                         ); end;");
        $stmt->bindParam(':P_ID_ROL_VACACION', $id_rol_vacacion, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_PERIODO_VAC_TRAB', $id_periodo_vac_trab, PDO::PARAM_STR);
        $stmt->bindParam(':P_FECHA_INI', $fecha_ini, PDO::PARAM_STR);
        $stmt->bindParam(':P_FECHA_FIN', $fecha_fin, PDO::PARAM_STR);
        $stmt->bindParam(':P_OPC', $opc, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_USER_REG', $id_user, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute();
        $return = [
            'nerror' => $nerror,
            'message' => $msgerror,
        ];
        return $return;
    }
    public static function listAprobeHeader($request)
    {
        $id_periodo_vac = $request->id_periodo_vac;
        $id_area = $request->id_area;
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_puesto = $request->id_puesto;
        $id_persona =  $request->id_persona;
        $estado =  $request->estado;
        $q = DB::table('moises.vw_trabajador as a');
            $q->join('eliseo.org_sede_area as d', 'a.id_sedearea', '=', 'd.id_sedearea');
            $q->leftjoin('eliseo.plla_periodo_vac_trab as b', 'a.id_persona', '=', DB::raw("b.id_persona and b.id_periodo_vac = " .$id_periodo_vac));
            $q->join('eliseo.plla_estado_vac_trab as c', 'b.id_estado_vac_trab', '=', 'c.id_estado_vac_trab');
            $q->where('d.id_area', $id_area);
            $q->where('d.id_entidad',  $id_entidad);
            $q->whereraw("d.id_depto like '" . $id_depto . "%'");
            if (strlen($id_puesto)>0) {
                $q->where('a.id_puesto', $id_puesto);
            }
            if (strlen($id_persona)>0) {
                $q->where('a.id_persona', $id_persona);
            }
            if (strlen($estado)>0) {
                $q->where('b.id_estado_vac_trab', $estado); //cambiando
            }
            $q->select(
                'a.id_persona',
                DB::raw("(a.nombre || ' ' || a.paterno || ' ' || a.materno ) as nombres"),
                'a.fecha_inicio',
                'a.fecha_fin_previsto',
                'b.id_periodo_vac_trab',
                'b.periodo_ini',
                'b.periodo_fin',
                'b.total_dias',
                'c.nombre as estado',
                'b.id_estado_vac_trab',
                'b.total_dias_efect',
                'b.comentario'
            );
            $q->orderBy('nombres', 'asc');
            $query = $q->get();

        $data = array();
        foreach ($query as $row) {
            $item = array();
            $item['id_persona'] = $row->id_persona;
            $item['nombres'] = $row->nombres;
            $item['comentario'] = $row->comentario;
            $item['fecha_inicio'] = $row->fecha_inicio;
            $item['fecha_fin_previsto'] = $row->fecha_fin_previsto;
            $item['periodo_ini'] = $row->periodo_ini;
            $item['periodo_fin'] = $row->periodo_fin;
            $item['total_dias'] = $row->total_dias;
            $item['total_dias_efect'] = $row->total_dias_efect;
            $item['id_periodo_vac_trab'] = $row->id_periodo_vac_trab;
            $item['estado'] = $row->estado;
            $item['id_estado_vac_trab'] = $row->id_estado_vac_trab;
            $item['details'] = HolidaysData::listAprobeChild($row->id_periodo_vac_trab);
            $data[] = $item;
        }

        return $data;
    }
    public static function listAprobeChild($id_periodo_vac_trab)
    {
        $query = DB::table('plla_rol_vacacional as a')
            ->join('eliseo.plla_estado_rol_vac as c', 'a.id_estado_rol_vac', '=', 'c.id_estado_rol_vac')
            ->join('users as b', 'a.id_user_reg', '=', 'b.id')
            ->where('a.id_periodo_vac_trab', $id_periodo_vac_trab)
            ->select(
                'a.id_rol_vacacion',
                'a.fecha_ini',
                'a.fecha_fin',
                'a.dias',
                'a.condicion',
                'c.nombre as estado',
                DB::raw("case when a.ID_PARENT is null then a.ID_ROL_VACACION else a.ID_PARENT end as orden,
            case when a.ID_PARENT is null  then 0 else 1 end as padre"),
                'a.id_parent',
                'a.fecha_registra',
                'a.id_user_reg',
                'b.email',
                'a.confirmacion_salida',
                'a.confirmacion_retorno',
                'a.dias_efect',
                'a.motivo_repro',
                'a.id_tipo_rol_vac'
            )
            ->orderBy('orden', 'asc')
            ->orderBy('a.fecha_ini', 'asc')
            ->get();
        return $query;
    }
    public static function updateAprobeHeaderChild($id_periodo_vac_trab,  $id_user_apru,  $fecha_apru, $request)
    {
        $comentario = $request->comentario;
        $estado = $request->estado;
        $result = DB::table('plla_periodo_vac_trab')
            ->where('id_periodo_vac_trab', $id_periodo_vac_trab)
            ->update([
                'comentario' => $comentario,
                'id_user_apru' => $id_user_apru,
                'fecha_apru' => $fecha_apru,
                'id_estado_vac_trab' => $estado,
            ]);
        if ($result) {
            $response = [
                'success' => true,
                'message' => '',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede modificar',
            ];
        }
        return $response;
    }

    public static function rescheduleVacation($id_user, $request)
    {
        // dd($id_user, $request);
        $nerror = 0;
        $msgerror = '';
        for ($i = 1; $i <= 200; $i++) {
            $msgerror .= '0';
        }
        $aid_rol_vacacion = array();
        $afecha_ini = array();
        $afecha_fin = array();
        $aopc = array();
        $amotivo_repro = array();
        $id_rol_vacacion_rep = $request->id_rol_vacacion_rep;
        $gth = $request->gth;
        foreach ($request->details as $datos) {
            $items = (object)$datos;
            $aid_rol_vacacion[] = $items->id_rol_vacacion;
            $afecha_ini[] = $items->fecha_ini;
            $afecha_fin[] = $items->fecha_fin;
            $aopc[] = $items->opc;
            $amotivo_repro[] = $items->motivo_repro;
        }

        $id_rol_vacacion = implode("*", $aid_rol_vacacion);
        $fecha_ini = implode("*", $afecha_ini);
        $fecha_fin = implode("*", $afecha_fin);
        $opc = implode("*", $aopc);
        $motivo_repro = implode("*", $amotivo_repro);

        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_HUMAN_TALENT_MGT.SP_GENERAR_REP_VACACIONAL(
                                    :P_ID_ROL_VACACION_REP, :P_ID_ROL_VACACION,:P_FECHA_INI,:P_FECHA_FIN, :P_MOTIVO_REPRO, :P_OPC, :P_ID_USER_REG, :P_GTH, :P_ERROR, :P_MSGERROR
                                         ); end;");

        $stmt->bindParam(':P_ID_ROL_VACACION_REP', $id_rol_vacacion_rep, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_ROL_VACACION', $id_rol_vacacion, PDO::PARAM_STR);
        $stmt->bindParam(':P_FECHA_INI', $fecha_ini, PDO::PARAM_STR);
        $stmt->bindParam(':P_FECHA_FIN', $fecha_fin, PDO::PARAM_STR);
        $stmt->bindParam(':P_MOTIVO_REPRO', $motivo_repro, PDO::PARAM_STR);
        $stmt->bindParam(':P_OPC', $opc, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_USER_REG', $id_user, PDO::PARAM_INT);
        $stmt->bindParam(':P_GTH', $gth, PDO::PARAM_STR);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute();
        $return = [
            'nerror' => $nerror,
            'message' => $msgerror,
        ];
        return $return;
    }
    public static function getRescheduleVacation($id_parent)
    {
        $query = DB::table('plla_rol_vacacional a')
            ->join('eliseo.plla_estado_rol_vac b', 'a.ID_ESTADO_ROL_VAC', '=', 'b.ID_ESTADO_ROL_VAC')
            ->where('a.id_parent', $id_parent)
            ->select(
                'a.id_rol_vacacion',
                DB::raw("to_char(a.fecha_ini, 'YYYY-MM-DD') as fecha_ini, to_char(a.fecha_fin, 'YYYY-MM-DD') as fecha_fin"),
                'a.condicion',
                'b.nombre as estado',
                'a.dias',
                'a.confirmacion_salida',
                'a.confirmacion_retorno',
                'a.motivo_repro',
                DB::raw("(case when a.fecha_ini < CURRENT_DATE +1 then 1 else 0 end) as fecha_calculado")
            )
            ->get();
        return $query;
    }
    public static function listPeriodHolidays($id_entidad, $nombre, $per_page)
    {
        // dd('ss', $id_entidad, $nombre, $per_page);
        $query = DB::table('plla_periodo_vac as a')
            ->join('plla_estado_periodo_vac as b', 'a.id_estado_periodo_vac', '=', 'b.id_estado_periodo_vac')
            ->where('a.id_entidad', $id_entidad)
            ->whereraw(ComunData::fnBuscar('a.nombre') . ' like ' . ComunData::fnBuscar("'%" . $nombre . "%'"))
            ->select('a.id_periodo_vac', 'a.nombre', 'a.comentario', 'a.min_dias_periodo1', 'a.id_estado_periodo_vac', 'a.anho_inicio', 'a.id_entidad', DB::raw("(b.nombre) as nombre_estado"))
            ->orderBy('a.anho_inicio', 'desc')
            ->paginate((int)$per_page);
        return $query;
    }
    public static function addPeriodHolidays($request)
    {

        $id_entidad = $request->id_entidad;
        $nombre = $request->nombre;
        $comentario = $request->comentario;
        $min_dias_periodo1 = $request->min_dias_periodo1;
        $id_estado_periodo_vac = $request->id_estado_periodo_vac;
        $anho_inicio = $request->anho_inicio;
        $count = DB::table('plla_periodo_vac')
            ->where('id_entidad', $id_entidad)
            ->where('anho_inicio', $anho_inicio)
            ->count();

        if ($count == 0) {
            $id_periodo_vac = ComunData::correlativo('plla_periodo_vac', 'id_periodo_vac');
            if ($id_periodo_vac > 0) {

                $save = DB::table('plla_periodo_vac')->insert(
                    [
                        'id_periodo_vac' =>  $id_periodo_vac,
                        'id_entidad' =>  $id_entidad,
                        'nombre' => $nombre,
                        'comentario' =>  $comentario,
                        'min_dias_periodo1' => $min_dias_periodo1,
                        'id_estado_periodo_vac' =>  $id_estado_periodo_vac,
                        'anho_inicio' => $anho_inicio,

                    ]
                );
                if ($save) {
                    $response = [
                        'success' => true,
                        'message' => '',
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'No se puede insertar',
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No se ha generado correlativo',
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'El item ya existe',
            ];
        }

        return $response;
    }
    public static function updatePeriodHolidays($id_periodo_vac, $request)
    {
        $id_entidad = $request->id_entidad;
        $nombre = $request->nombre;
        $comentario = $request->comentario;
        $min_dias_periodo1 = $request->min_dias_periodo1;
        $id_estado_periodo_vac = $request->id_estado_periodo_vac;
        $anho_inicio = $request->anho_inicio;
        $result = DB::table('plla_periodo_vac')
            ->where('id_periodo_vac', $id_periodo_vac)
            ->update([
                'id_entidad' => $id_entidad,
                'nombre' => $nombre,
                'comentario' => $comentario,
                'min_dias_periodo1' => $min_dias_periodo1,
                'id_estado_periodo_vac' => $id_estado_periodo_vac,
                'anho_inicio' => $anho_inicio
            ]);

        if ($result) {
            $response = [
                'success' => true,
                'message' => '',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede modificar',
            ];
        }
        return $response;
    }
    public static function deletePeriodHolidays($id_periodo_vac)
    {
        $query = DB::table('plla_periodo_vac')->where('id_periodo_vac', $id_periodo_vac)->delete();
        return $query;
    }
    public static function agregarProceGeneratePerioVacMasivo($request)
    {
     
        $nerror = 0;
        $msgerror = '';
        for ($i = 1; $i <= 200; $i++) {
            $msgerror .= '0';
        }
        $id_area = $request->id_area;
        $id_periodo_vac = $request->id_periodo_vac;
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_depto_parent = $request->id_depto_parent;
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_HUMAN_TALENT_MGT.SP_GENERAR_PERIODO_VAC_MASIVO(
                                :P_ID_ENTIDAD, :P_ID_DEPTO_PARENT, :P_ID_AREA, :P_ID_PERIODO_VAC, :P_ID_DEPTO, :P_ERROR, :P_MSGERROR
                                     ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO_PARENT', $id_depto_parent, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_AREA', $id_area, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PERIODO_VAC', $id_periodo_vac, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute();
        $return = [
            'nerror' => $nerror,
            'msgerror' => $msgerror,
        ];
        return $return;
    }

    public static function updateVacacionesConfirm($id_rol_vacacion,  $fecha_confirmacion,  $user_confirmacion, $request)
    {
        // dd($id_rol_vacacion,  $fecha_confirmacion,  $user_confirmacion, $request);
        $confirmacion_salida = $request->confirmacion_salida;
        $dias_efect = $request->dias_efect;
        $result = DB::table('eliseo.plla_rol_vacacional')
            ->where('id_rol_vacacion', $id_rol_vacacion)
            ->update([
                'dias_efect' => $dias_efect,
                'confirmacion_salida' => $confirmacion_salida,
                'fecha_confirmacion_salida' => $fecha_confirmacion,
                'user_confirmacion_salida' => $user_confirmacion,
            ]);
        if ($result) {
            $response = [
                'success' => true,
                'message' => '',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede modificar',
            ];
        }
        return $response;
    }
    public static function updateRetornoVacacionesConfirm($id_rol_vacacion,  $fecha_confirmacion,  $user_confirmacion, $request)
    {
        // dd($id_rol_vacacion,  $fecha_confirmacion,  $user_confirmacion, $request);
        $confirmacion_retorno = $request->confirmacion_retorno;
        $result = DB::table('eliseo.plla_rol_vacacional')
            ->where('id_rol_vacacion', $id_rol_vacacion)
            ->update([
                'confirmacion_retorno' => $confirmacion_retorno,
                'fecha_confirmacion_retorno' => $fecha_confirmacion,
                'user_confirmacion_retorno' => $user_confirmacion,
            ]);
        if ($result) {
            $response = [
                'success' => true,
                'message' => '',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede modificar',
            ];
        }
        return $response;
    }

    public static function addSolicitudHolidays($request, $id_user_reg, $fecha_reg)
    {

        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_persona = $request->id_persona;
        $mensaje = $request->mensaje;
        $id_estado_vac_adel = $request->id_estado_vac_adel;
        $detalle = $request->details;

        // $count = DB::table('eliseo.plla_sol_vac_adel')
        //     ->where('id_entidad', $id_entidad)
        //     ->where('id_depto', $id_depto)
        //     ->where('id_persona', $id_persona)
        //     ->count();

        // if ($count == 0) {
            $id_sol_vac_adel = ComunData::correlativo('eliseo.plla_sol_vac_adel', 'id_sol_vac_adel');
            if ($id_sol_vac_adel > 0) {

                $save = DB::table('eliseo.plla_sol_vac_adel')->insert(
                    [
                        'id_sol_vac_adel' =>  $id_sol_vac_adel,
                        'id_entidad' =>  $id_entidad,
                        'id_depto' => $id_depto,
                        'id_persona' =>  $id_persona,
                        'mensaje' => $mensaje,
                        'id_estado_vac_adel' =>  $id_estado_vac_adel,
                        'id_user_reg' => $id_user_reg,
                        'fecha_reg' => $fecha_reg,
                    ]
                );
                if ($save) {
                    foreach($detalle as $datos) {
                        $items = (object)$datos;
                        $id_sol_vac_adel_det = ComunData::correlativo('eliseo.plla_sol_vac_adel_det', 'id_sol_vac_adel_det');
                        if ($id_sol_vac_adel_det > 0) {
                        $deta = DB::table('eliseo.plla_sol_vac_adel_det')->insert(
                            [
                                'id_sol_vac_adel_det' =>  $id_sol_vac_adel_det,
                                'id_sol_vac_adel' =>  $id_sol_vac_adel,
                                'desde' =>  $items->fecha_ini,
                                'hasta' => $items->fecha_fin,
                                'comentario' =>  $items->comentario,
                                'total_dias' => $items->dias,
                                'id_user_reg' => $id_user_reg,
                                'fecha_reg' => $fecha_reg,
                            ]
                        );
                    } else {
                        $response = [
                            'success' => false,
                            'message' => 'No se ha generado correlativo',
                        ];
                    }
                    }

                }
                if ($save) {
                    $response = [
                        'success' => true,
                        'message' => 'Se creó satisfatoriamente',
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'No se puede insertar',
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No se ha generado correlativo',
                ];
            }
        // } else {
        //     $response = [
        //         'success' => false,
        //         'message' => 'Ya tiene una solicitud creada',
        //     ];
        // }

        return $response;
    }
    public static function listReques($request, $id_user) {
        $id_entidad =$request ->id_entidad;
        $id_depto = $request->id_depto;
        $id_estado_vac_adel = $request->id_estado_vac_adel;
        $id_area = $request->id_area;
        $persona = $request->persona;
        $per_page = $request->per_page;
        
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

        $q = DB::table('moises.vw_trabajador a');
        $q->join('eliseo.plla_sol_vac_adel x', 'a.id_persona', '=', 'x.id_persona');
        $q->join('eliseo.plla_estado_vac_adel ea', 'x.id_estado_vac_adel', '=', 'ea.id_estado_vac_adel'); 
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
        if (strlen($id_area) > 0) {
            $q->where('b.id_area', $id_area);
        }
        if (strlen($id_estado_vac_adel) > 0) {
            $q->where('ea.id_estado_vac_adel', $id_estado_vac_adel);
        }
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
            'ea.nombre as estado',
            'ea.id_estado_vac_adel',
            'x.id_sol_vac_adel'
        );
        $q->orderBy('a.nombre', 'asc');
        $data = $q->paginate((int) $per_page);
        //  ->get();
        return $data;
    }
    public static function showRequest($request) {
        // $id_entidad =$request ->id_entidad;
        // $id_depto = $request->id_depto;
        $id_sol_vac_adel = $request->id_sol_vac_adel;
        // $id_persona = $request->id_persona;

        $q = DB::table('moises.vw_trabajador a');
        $q->join('eliseo.plla_sol_vac_adel x', 'a.id_persona', '=', 'x.id_persona'); //agrgado recientemente
        $q->join('eliseo.plla_estado_vac_adel y', 'x.id_estado_vac_adel', '=', 'y.id_estado_vac_adel'); //agrgado recientemente
        $q->join('moises.persona_natural z', 'z.id_persona', '=', 'a.id_persona');
        $q->join('org_sede_area b', 'a.id_sedearea', '=', 'b.id_sedearea');
        $q->join('moises.tipo_documento c', 'a.id_tipodocumento', '=', 'c.id_tipodocumento');
        $q->join('moises.situacion_trabajador d', 'a.id_situacion_trabajador', '=', 'd.id_situacion_trabajador');
        $q->join('moises.condicion_laboral e', 'a.id_condicion_laboral', '=', 'e.id_condicion_laboral');
        $q->join('moises.tipo_tiempo_trabajo f', 'a.id_tipo_tiempo_trabajo', '=', 'f.id_tipo_tiempo_trabajo');
        $q->join('org_area g', 'b.id_area', '=', 'g.id_area');
        $q->leftjoin('plla_tipo_horario th', 'th.id_tipo_horario', '=', 'a.id_tipo_horario');
        $q->leftjoin('moises.tipo_control_personal tc', 'tc.id_tipo_control_personal', '=', 'a.id_tipo_control_personal');
        $q->leftJoin('eliseo.plla_puesto pue', 'a.id_puesto', '=', 'pue.id_puesto');
        // $q->where('b.id_entidad', $id_entidad);
        // $q->whereraw("b.id_depto like '" . $id_depto . "%'");
        // $q->where('x.id_persona', $id_persona);
        $q->where('x.id_sol_vac_adel', $id_sol_vac_adel);
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
            'z.foto',
            'x.id_estado_vac_adel',
            'y.nombre as estado',
            'x.mensaje',
            'x.respuesta',
            'x.id_entidad',
            'x.id_depto'
        );
        $q->orderBy('a.nombre', 'asc');
        $data = $q->first();
        return $data;
    }
    public static function listAdelantoDetalle($id_sol_vac_adel) { 
        $query = DB::table('eliseo.plla_sol_vac_adel_det a')
                        ->leftJoin('eliseo.plla_rol_vacacional b', 'a.id_sol_vac_adel_det', '=', 'b.id_sol_vac_adel_det')
                        ->leftJoin('eliseo.plla_periodo_vac_trab c', 'b.id_periodo_vac_trab', '=', 'c.id_periodo_vac_trab')
                        ->where('a.id_sol_vac_adel', $id_sol_vac_adel)
                        ->select('a.id_sol_vac_adel_det', 'a.id_sol_vac_adel',
                                 DB::raw("to_char(a.desde, 'YYYY-MM-DD') as desde"),
                                 DB::raw("to_char(a.hasta, 'YYYY-MM-DD') as hasta"),
                                 'a.comentario', 'a.total_dias', 'c.id_periodo_vac')
                        ->orderBy('a.desde', 'asc')
                        ->get();
        return $query;
    }
    public static function updateSolicitudHolidays($id_sol_vac_adel, $request, $id_user_reg, $fecha_reg)
    {

        $mensaje = $request->mensaje;
        $id_estado_vac_adel = $request->id_estado_vac_adel;
        $detalle = $request->details;


                $save = DB::table('eliseo.plla_sol_vac_adel')
                        ->where('id_sol_vac_adel', $id_sol_vac_adel)
                        ->update(
                    [
                        'mensaje' => $mensaje,
                        'id_estado_vac_adel' =>  $id_estado_vac_adel,
                        'id_user_mod' => $id_user_reg,
                        'fecha_mod' => $fecha_reg,
                    ]
                );
                if ($save) {
                    foreach($detalle as $datos) {
                        $items = (object)$datos;

                        $deta = DB::table('eliseo.plla_sol_vac_adel_det')
                                ->where('id_sol_vac_adel_det', $items->id_sol_vac_adel_det)
                                ->update(
                            [
                                'desde' =>  $items->desde,
                                'hasta' => $items->hasta,
                                'comentario' =>  $items->comentario,
                                'total_dias' => $items->total_dias,
                                'id_user_mod' => $id_user_reg,
                                'fecha_mod' => $fecha_reg,
                            ]
                        );
                    }

                }
                if ($save) {
                    $response = [
                        'success' => true,
                        'message' => 'Se modificó satisfatoriamente',
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'No se puede insertar',
                    ];
                }

        return $response;
    }
    public static function deleteSolDetalle($id_sol_vac_adel_det) {

        $delete = DB::table('eliseo.plla_sol_vac_adel_det')
            ->where('id_sol_vac_adel_det', $id_sol_vac_adel_det)
            ->delete();

        if($delete){
            $response=[
                'success'=> true,
                'message'=>'La se elimino satisfactoriamente',
            ];
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se puede eliminar',
            ];
        }  
        return $response;
    }
    public static function deleteSolicitud($id_sol_vac_adel) {

        $del = DB::table('eliseo.plla_sol_vac_adel_det')
        ->where('id_sol_vac_adel', $id_sol_vac_adel)
        ->delete();

        if ($del) {
            $delete = DB::table('eliseo.plla_sol_vac_adel')
                ->where('id_sol_vac_adel', $id_sol_vac_adel)
                ->delete();
        }

        if($delete){
            $response=[
                'success'=> true,
                'message'=>'La se elimino satisfactoriamente',
            ];
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se puede eliminar',
            ];
        }  
        return $response;
    }
    public static function refusedAlularSolicitud($id_sol_vac_adel, $request, $id_user_reg, $fecha_reg){
        $id_estado_vac_adel = $request->id_estado_vac_adel;
        $respuesta = $request->respuesta;
        $result = DB::table('eliseo.plla_sol_vac_adel')
            ->where('id_sol_vac_adel', $id_sol_vac_adel)
            ->update([
                'respuesta' => $respuesta,
                'id_estado_vac_adel' =>  $id_estado_vac_adel,
                'id_user_mod' => $id_user_reg,
                'fecha_mod' => $fecha_reg,
            ]);
        if ($result) {
            $response = [
                'success' => true,
                'message' => 'Se modifico satisfactoriamente',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No se puede modificar',
            ];
        }
        return $response;
    }
    public static function agregarAdelantoVacacional($request, $id_user_reg, $fecha_reg)
    {
        $it = 1;
        $nerror = 0;
        $msgerror = '200';
        for ($i = 1; $i <= 200; $i++) {
            $msgerror .= '0';
        }
        $detalle = $request->details;
        $id_persona = $request->id_persona;
        $respuesta = $request->respuesta;
        $id_estado_vac_adel = '02';
        $id_sol_vac_adel = $request->id_sol_vac_adel;
        foreach($detalle as $datos) {
            $items = (object)$datos;
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_HUMAN_TALENT_MGT.SP_GENERAR_VAC_ADELANTO(
                                    :P_ID_PERIODO_VAC,
                                    :P_ID_SOL_VAC_ADEL_DET,
                                    :P_ID_PERSONA,
                                    :P_FECHA_INI,
                                    :P_FECHA_FIN,
                                    :P_ID_USER_REG,
                                    :P_ITEM,
                                    :P_ERROR,
                                    :P_MSGERROR); end;");
            $stmt->bindParam(':P_ID_PERIODO_VAC', $items->id_periodo_vac, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_SOL_VAC_ADEL_DET', $items->id_sol_vac_adel_det, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
            $stmt->bindParam(':P_FECHA_INI', $items->desde, PDO::PARAM_STR);
            $stmt->bindParam(':P_FECHA_FIN', $items->hasta, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_USER_REG', $id_user_reg, PDO::PARAM_INT);
            $stmt->bindParam(':P_ITEM', $it, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
            $stmt->execute();
            $return = [
                'nerror' => $nerror,
                'msgerror' => $msgerror,
            ];

            if($nerror!=0){
            break;
          }
          $it++;
        }
        if ($nerror == 0) {
            $result = DB::table('eliseo.plla_sol_vac_adel')
            ->where('id_sol_vac_adel', $id_sol_vac_adel)
            ->update([
                'respuesta' => $respuesta,
                'id_estado_vac_adel' =>  $id_estado_vac_adel,
                'id_user_mod' => $id_user_reg,
                'fecha_mod' => $fecha_reg,
            ]);
        }
        return $return;
    }
    public static function listTrabajadorHolidays($request, $id_user)
    {
        $id_entidad =$request ->id_entidad;
        $id_depto = $request->id_depto;
        $id_periodo_vac = $request->id_periodo_vac;
        $id_area = $request->id_area;
        $estado = $request->estado;
        $persona = $request->persona;
        $per_page = $request->per_page;
        $adelanto = $request->adelanto;

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
        if (strlen($id_area) > 0) {
            $q->where('b.id_area', $id_area);
        }
        if (strlen($estado) > 0) {
            if ($adelanto == 'S') {
                $q->whereraw("(ev.id_estado_vac_trab='".$estado."' or x.id_periodo_vac_trab in(
                    select r.id_periodo_vac_trab from plla_rol_vacacional r, plla_periodo_vac_trab pt 
                    where r.id_periodo_vac_trab=pt.id_periodo_vac_trab
                    and pt.id_periodo_vac=".$id_periodo_vac."
                    and r.id_tipo_rol_vac='A'
                    ))");
            } else {
                $q->where('ev.id_estado_vac_trab', $estado);
            }
           
        }
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
}
