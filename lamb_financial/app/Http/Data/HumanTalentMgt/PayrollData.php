<?php

namespace App\Http\Data\HumanTalentMgt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\HumanTalentMgt\ComunData;
use DateTime;
use Exception;
use Mockery\Undefined;

class PayrollData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function ListSalaryScale($id_entidad, $id_depto, $id_anho, $id_nivel, $perpage)
    {

        $fmr = ComunData::getParameter('PARAM_FMR_ESCALA', $id_entidad, $id_anho);
        $rmv = ComunData::getParameter('PARAM_RMV', $id_entidad, $id_anho);

        $q = DB::table('plla_escala_salarial as a');
        $q->join('plla_grupo_escala as e', 'e.id_grupo_escala', '=', 'a.id_grupo_escala');
        $q->join('plla_nivel as b', 'b.id_nivel', '=', 'e.id_nivel');
        $q->join('plla_grado as c', 'c.id_grado', '=', 'e.id_grado');
        $q->join('plla_subgrado as d', 'd.id_subgrado', '=', 'e.id_subgrado');
        $q->where('a.id_entidad', $id_entidad);
        $q->where('a.id_depto', $id_depto);
        $q->where('a.id_anho', $id_anho);
        if (strlen($id_nivel) > 0) {
            $q->where('e.id_nivel', $id_nivel);
        }
        $q->select(
            'a.id_escala_salarial',
            'a.id_grupo_escala',
            'a.id_entidad',
            'a.id_anho',
            'a.id_depto',
            'a.tipo_min',
            'a.fmr',
            'e.nombre',
            'b.nombre as nivel',
            'c.nombre as grado',
            'd.nombre as subgrado',
            'a.comentario',
            'a.puntminimo',
            'a.puntmaximo',
            DB::raw("case when a.tipo_min = '1' then " . $rmv . " else " . $fmr . "*(coalesce(a.puntminimo,0)/100) end as impminimo"),
            DB::raw("" . $fmr . "*(coalesce(a.puntmaximo,0)/100) as impmaximo")
        );
        $q->orderBy('e.id_nivel', 'asc');
        $q->orderBy('e.id_grado', 'asc');
        $q->orderBy('e.id_subgrado', 'asc');
        $q->orderBy('e.nombre', 'asc');

        $data = $q->paginate($perpage);

        return $data;
    }

    public static function AddSalaryScale($request)
    {

        $detail = $request->detail;

        foreach ($detail as $datos) {
            $items = (object)$datos;
            if ($items->opcionreal == '1') {
                $id_escala_salarial =  ComunData::correlativo('plla_escala_salarial', 'id_escala_salarial');
                DB::table('plla_escala_salarial')->insert(
                    [
                        'id_escala_salarial' => $id_escala_salarial,
                        'id_grupo_escala' => $items->id_grupo_escala,
                        'id_entidad' => $request->id_entidad,
                        'id_anho' => $request->id_anho,
                        'id_depto' => $request->id_depto,
                        'tipo_min' => '0',
                        'puntminimo' => 0,
                        'puntmaximo' => 0,
                    ]
                );
            }
        }
    }
    public static function UpdateSalaryScale($request)
    {

        $detail = $request->detail;
        $FMR    = $request->FMR;

        $error = 0;
        foreach ($detail as $datos) {
            $items = (object) $datos;
            $puntminimo = 0;
            if (strlen($puntminimo) > 0) {
                $puntminimo = $items->puntminimo;
            }
            $puntmaximo = 0;
            if (strlen($puntminimo) > 0) {
                $puntmaximo = $items->puntmaximo;
            }
            if ($puntminimo > $puntmaximo) {
                $error++;
            }
        }

        if ($error > 0) {
            $response = [
                'success' => false,
                'message' => 'Pje mínimo es mayor a pje máximo',
            ];
            return $response;
        } else {
            foreach ($detail as $datos) {
                $items = (object) $datos;
                $puntminimo = 0;
                if (strlen($puntminimo) > 0) {
                    $puntminimo = $items->puntminimo;
                }
                $puntmaximo = 0;
                if (strlen($puntminimo) > 0) {
                    $puntmaximo = $items->puntmaximo;
                }

                $affected = DB::table('plla_escala_salarial')
                    ->where('id_escala_salarial', $items->id_escala_salarial)
                    ->update([
                        //'tipo_min'=>$items->tipo_min,
                        'fmr' => $FMR,
                        'puntminimo' => $puntminimo,
                        'puntmaximo' => $puntmaximo
                    ]);
            }
        }
        $response = [
            'success' => true,
            'message' => ''
        ];
        return $response;
    }
    public static function DeleteSalaryScale($id_escala_salarial)
    {
        $scalegroup = DB::table('plla_escala_salarial')->where('id_escala_salarial', $id_escala_salarial)->first();
        if (!empty($scalegroup)) {

            $rows = DB::table('plla_escala_salarial')->where('id_escala_salarial', $id_escala_salarial)->delete();
            if ($rows > 0) {
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
        } else {
            $response = [
                'success' => false,
                'message' => 'No se ha encontrado escala salarial para eliminar',
            ];
        }
        return $response;
    }
    public static function ListsPensionScheme()
    {

        $data = DB::table('plla_regimen_pensionaria')
            ->where('vigencia', 1)
            ->select(
                'id_regimen_pensionaria',
                'nombre',
                'nombre_corto',
                'rem_max_ase',
                'fondo',
                'seguro',
                'tipo'
            )
            ->orderBy('id_regimen_pensionaria', 'asc')
            ->get();
        $datos = array();
        $i = 0;
        foreach ($data as $row) {
            $items = array();
            $items['num'] = $i;
            $items['id_regimen_pensionaria'] = $row->id_regimen_pensionaria;
            $items['nombre'] = $row->nombre;
            $items['nombre_corto'] = $row->nombre_corto;
            $items['fondo'] = $row->fondo;
            $items['seguro'] = $row->seguro;
            $items['fondoe'] = $row->fondo;
            $items['seguroe'] = $row->seguro;
            $items['tipo'] = $row->tipo;
            $items['rem_max_ase'] = $row->rem_max_ase;
            $items['rem_max_asee'] = $row->rem_max_ase;
            $items['edit'] = '0';
            $ditems = DB::table('plla_comision_pensionaria as e')
                ->join('plla_tipo_comision_pens as b', 'b.id_tipo_comision_pens', '=', 'e.id_tipo_comision_pens')
                ->where('e.id_regimen_pensionaria', $row->id_regimen_pensionaria)
                ->where('b.vigencia', 1)
                ->where('e.vigencia', 1)
                ->select(
                    'e.id_regimen_pensionaria',
                    'b.id_tipo_comision_pens',
                    'b.nombre',
                    'b.tipo',
                    'e.comision',
                    'e.comision as comisione',
                    DB::raw("case when b.tipo='F' then '' else 'Comisión Mixta: ' end as destipo"),
                    DB::raw("(ROW_NUMBER() OVER (ORDER BY e.id_tipo_comision_pens))+ " . $i . " as num,'0' as edit")
                )
                ->orderBy('b.tipo', 'asc')
                ->orderBy('e.id_regimen_pensionaria', 'asc')
                ->orderBy('e.id_tipo_comision_pens', 'asc')
                ->get();
            if (count($ditems) > 0) {
                $i = $i + count($ditems) + 1;
            } else {
                $i++;
            }
            $items['details'] = $ditems;
            $datos[] =  $items;
        }



        return $datos;
    }
    public static function  updatePensionScheme($id_regimen_pensionaria, $request)
    {

        if ($request->tipo == 'C') {
            $result = DB::table('plla_regimen_pensionaria')
                ->where('id_regimen_pensionaria', $id_regimen_pensionaria)
                ->update([
                    'seguro' => $request->seguro,
                    'fondo' => $request->fondo,
                    'rem_max_ase' => $request->rem_max_ase
                ]);
        } else {
            $result = DB::table('plla_comision_pensionaria')
                ->where('id_regimen_pensionaria', $id_regimen_pensionaria)
                ->where('id_tipo_comision_pens', $request->id_tipo_comision_pens)
                ->update([
                    'comision' => $request->comision
                ]);
        }
        if ($result > 0) {
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

    public static function getSearchPersonAssign($id_entidad, $id_depto, $id_tipo_pago_mensual, $datos, $perpage)
    {
        $data = DB::table('moises.vw_trabajador as a')
            ->join('moises.condicion_laboral as c', 'c.id_condicion_laboral', '=', 'a.id_condicion_laboral')
            ->whereraw("a.id_sedearea in(
                            select x.id_sedearea from org_sede_area x
                            where x.id_entidad=" . $id_entidad . "
                            and x.id_depto like '" . $id_depto . "%'
                        )")
            ->where('a.id_situacion_trabajador', '1')
            ->whereraw("a.id_persona not in(
                            select y.id_persona from plla_listado_general_pago y
                            where y.id_entidad=" . $id_entidad . "
                            and y.id_tipo_pago_mensual = " . $id_tipo_pago_mensual . "
                        )")
            ->whereraw(DB::raw("((" . ComunData::fnBuscar("a.nombre") . ' like ' . ComunData::fnBuscar("'%" . $datos . "%'") . ")
                    or (" . ComunData::fnBuscar("a.nombre ||' ' || a.paterno") . ' like ' . ComunData::fnBuscar("'%" . $datos . "%'") . ")
                    or (" . ComunData::fnBuscar("a.num_documento") . ' like ' . ComunData::fnBuscar("'%" . $datos . "%'") . ")
                    or (" . ComunData::fnBuscar("a.nombre ||' ' || a.paterno||' ' || a.materno") . ' like ' . ComunData::fnBuscar("'%" . $datos . "%'") . ")
                    or (" . ComunData::fnBuscar("a.paterno ||' ' || a.materno") . ' like ' . ComunData::fnBuscar("'%" . $datos . "%'") . "))"))
            ->select(
                'a.id_persona',
                'c.nombre as condicion_laboral',
                DB::raw("a.nombre||' '||a.paterno||' '||a.materno as nombrecompleto")
            )
            ->orderBy('nombrecompleto', 'asc')
            ->paginate($perpage);


        return $data;
    }
    public static function ListGeneralPayment($id_tipo_pago_mensual, $id_entidad, $id_depto, $id_anho, $id_mes)
    {

        $data = DB::table('plla_listado_general_pago as a')
            ->join('moises.vw_trabajador as t', 't.id_persona', '=', 'a.id_persona')
            ->join('org_sede_area sa', 'sa.id_sedearea', '=', 't.id_sedearea')
            ->join('org_area ar', 'ar.id_area', '=', 'sa.id_area')
            ->leftjoin('plla_tipo_kilometraje k', 'k.id_tipo_kilometraje', '=', 'a.id_tipo_kilometraje')
            ->join('moises.condicion_laboral as c', 'c.id_condicion_laboral', '=', 't.id_condicion_laboral')
            ->join('moises.situacion_trabajador as st', 'st.id_situacion_trabajador', '=', 't.id_situacion_trabajador')
            ->where('a.id_entidad', $id_entidad)
            ->where('a.id_tipo_pago_mensual', $id_tipo_pago_mensual)
            ->whereraw("sa.id_depto like '" . $id_depto . "%'")
            ->select(
                'a.id_tipo_pago_mensual',
                'a.id_entidad',
                'a.id_persona',
                'a.id_tipo_kilometraje',
                'a.sustento',
                'k.importe as imp_kilometraje',
                'k.nombrecorto',
                'a.imp_combustible',
                'a.imp_combustible as imp_combustiblee',
                'a.importe',
                'a.importe as importee',
                'c.nombre as condicion_laboral',
                'st.nombre_corto as situacion_trabajador',
                'ar.nombre as area',
                'c.id_condicion_laboral',
                't.id_situacion_trabajador',
                'a.consumo_comb',
                'a.mantenimiento',
                'a.seguro',
                'a.depreciacion',
                DB::raw("t.paterno||' '||t.materno||' '||t.nombre as nombrecompleto,'' as eliminar, '0' as edit"),
                DB::raw("(select count(*) from plla_listado_mensual_pago x 
                            where x.id_tipo_pago_mensual=a.id_tipo_pago_mensual
                            and x.id_entidad=a.id_entidad
                            and x.id_persona=a.id_persona
                            and x.id_anho=" . $id_anho . "
                            and x.id_mes=" . $id_mes . ") as asignado")
            )
            ->orderBy('nombrecompleto', 'asc')
            ->get();
        return $data;
    }
    public static function ListGeneralPaymentValid($request)
    {

        $obj = DB::table('plla_tipo_pago_mensual as a')
            ->join('plla_concepto_planilla as p', 'p.id_concepto_planilla', '=', 'a.id_concepto_planilla')
            ->where('a.id_tipo_pago_mensual', $request->id_tipo_pago_mensual)
            ->select('p.codigo', DB::raw("coalesce(a.consumo_comb,0) as consumo_comb,
                            coalesce(a.mantenimiento,0) as mantenimiento,
                            coalesce(a.seguro,0) as seguro,
                            coalesce(a.depreciacion,0) as depreciacion,
                            coalesce(a.combustible,0) as combustible"))
            ->first();

        $combustible = 0;
        $consumo_comb = 0;
        $mantenimiento = 0;
        $seguro = 0;
        $depreciacion = 0;

        if ($obj->codigo == 'BON_DEVE') {
            $combustible = $obj->combustible;
            $consumo_comb = $obj->consumo_comb;
            $mantenimiento = $obj->mantenimiento;
            $seguro = $obj->seguro;
            $depreciacion = $obj->depreciacion;
        }
        $data = array();
        $detail = $request->details;
        foreach ($detail as $datos) {
            $items = (object)$datos;

            $importe = $items->importe;
            $imp_kilometraje = 0;
            $kilometraje = '';
            if ($obj->codigo === 'BON_DEVE') {
                $obj_km = DB::table('plla_tipo_kilometraje')->where('id_tipo_kilometraje', $items->id_tipo_kilometraje)->select('importe', 'nombrecorto')->first();
                if (!empty($obj_km)) {
                    $importe = (($mantenimiento + $seguro + $depreciacion) + ($combustible / $consumo_comb)) * $obj_km->importe;
                    $imp_kilometraje = $obj_km->importe;
                    $kilometraje = $obj_km->nombrecorto;
                } else {
                    $importe = 0;
                }
            }

            $item = array();

            $obj_val = DB::table('moises.vw_trabajador as t')
                ->join('moises.condicion_laboral as c', 'c.id_condicion_laboral', '=', 't.id_condicion_laboral')
                ->where('t.id_persona', $items->id_persona)
                ->select(
                    'c.nombre as condicion_laboral',
                    DB::raw("t.paterno||' '||t.materno||' '||t.nombre as nombrecompleto")
                )->first();
            if (!empty($obj_val)) {
                $item['nombrecompleto'] = $obj_val->nombrecompleto;
                $item['condicion_laboral'] = $obj_val->condicion_laboral;
                $contar = DB::table('plla_listado_general_pago')
                    ->where('id_entidad', $request->id_entidad)
                    ->where('id_tipo_pago_mensual', $request->id_tipo_pago_mensual)
                    ->where('id_persona', $items->id_persona)
                    ->count();
                if ($contar == 0) {
                    $item['valid'] = 'S';
                } else {
                    $item['valid'] = 'E';
                }
            } else {
                $item['nombrecompleto'] = $items->nombrecompleto;
                $item['condicion_laboral'] = '';
                $item['condicion_laboral'] = '';
                $item['valid'] = 'N';
            }
            $item['id_anho'] = '';
            $item['id_mes'] = '';
            $item['importe'] = $importe;
            if ($obj->codigo === 'BON_DEVE') {
                $item['id_tipo_kilometraje'] = $items->id_tipo_kilometraje;
            } else {
                $item['id_tipo_kilometraje'] = null;
            }
            $item['kilometraje'] = $kilometraje;
            $item['imp_combustible'] = $combustible;
            $item['sustento'] = $request->sustento;
            $item['id_persona'] = $items->id_persona;
            $item['imp_kilometraje'] = $imp_kilometraje;

            $data[] = $item;
        }
        return $data;
    }
    public static function addGeneralPayment($request)
    {

        $detail = $request->detail;

        $obj = DB::table('plla_tipo_pago_mensual as a')
            ->join('plla_concepto_planilla as p', 'p.id_concepto_planilla', '=', 'a.id_concepto_planilla')
            ->where('a.id_tipo_pago_mensual', $request->id_tipo_pago_mensual)
            ->select('p.codigo', DB::raw("coalesce(a.consumo_comb,0) as consumo_comb,
                            coalesce(a.mantenimiento,0) as mantenimiento,
                            coalesce(a.seguro,0) as seguro,
                            coalesce(a.depreciacion,0) as depreciacion,
                            coalesce(a.combustible,0) as combustible"))
            ->first();

        $combustible = 0;
        $consumo_comb = 0;
        $mantenimiento = 0;
        $seguro = 0;
        $depreciacion = 0;

        if ($obj->codigo == 'BON_DEVE') {
            $combustible = $obj->combustible;
            $consumo_comb = $obj->consumo_comb;
            $mantenimiento = $obj->mantenimiento;
            $seguro = $obj->seguro;
            $depreciacion = $obj->depreciacion;
        }

        foreach ($detail as $datos) {
            $items = (object)$datos;

            $importe = $items->importe;

            if ($obj->codigo === 'BON_DEVE') {
                $importe = (($mantenimiento + $seguro + $depreciacion) + ($combustible / $consumo_comb)) * $items->imp_kilometraje;
            }
            DB::table('plla_listado_general_pago')->insert(
                [
                    'id_tipo_pago_mensual' => $request->id_tipo_pago_mensual,
                    'id_entidad' => $request->id_entidad,
                    'id_persona' => $items->id_persona,
                    'id_tipo_kilometraje' => $items->id_tipo_kilometraje,
                    'sustento' => $items->sustento,
                    'imp_kilometraje' => $items->imp_kilometraje,
                    'imp_combustible' => $combustible,
                    'importe' => $importe,
                    'consumo_comb' => $consumo_comb,
                    'mantenimiento' => $mantenimiento,
                    'seguro' => $seguro,
                    'depreciacion' => $depreciacion
                ]
            );
        }
    }

    public static function  updateGeneralPayment($id_persona, $request)
    {


        $result = DB::table('plla_listado_general_pago')
            ->where('id_persona', $id_persona)
            ->where('id_tipo_pago_mensual', $request->id_tipo_pago_mensual)
            ->where('id_entidad', $request->id_entidad)
            ->update([
                'importe' => $request->importe,
                'imp_kilometraje' => $request->imp_kilometraje,
                'imp_combustible' => $request->imp_combustible,
            ]);

        if ($result > 0) {
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
    public static function updateGeneralPaymentAll($id_tipo_pago_mensual, $request)
    {


        $obj = DB::table('plla_tipo_pago_mensual as a')
            ->join('plla_concepto_planilla as p', 'p.id_concepto_planilla', '=', 'a.id_concepto_planilla')
            ->where('a.id_tipo_pago_mensual', $id_tipo_pago_mensual)
            ->select('p.codigo', DB::raw("coalesce(a.consumo_comb,0) as consumo_comb,
                            coalesce(a.mantenimiento,0) as mantenimiento,
                            coalesce(a.seguro,0) as seguro,
                            coalesce(a.depreciacion,0) as depreciacion,
                            coalesce(a.combustible,0) as combustible"))
            ->first();

        $combustible = 0;
        $consumo_comb = 0;
        $mantenimiento = 0;
        $seguro = 0;
        $depreciacion = 0;

        $importe = $request->importe;

        if ($obj->codigo == 'BON_DEVE') {
            $combustible = $request->imp_combustible;
            $consumo_comb = $obj->consumo_comb;
            $mantenimiento = $obj->mantenimiento;
            $seguro = $obj->seguro;
            $depreciacion = $obj->depreciacion;

            $importe = (($mantenimiento + $seguro + $depreciacion) + ($combustible / $consumo_comb));
        }

        $result = DB::table('plla_listado_general_pago')
            ->where('id_tipo_pago_mensual', $request->id_tipo_pago_mensual)
            ->where('id_entidad', $request->id_entidad)
            ->update([
                'importe' => DB::raw("case when '" . $obj->codigo . "'='BON_DEVE' then " . $importe . "*imp_kilometraje else " . $importe . " end")
            ]);

        if ($result > 0) {
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
    public static function DeleteGeneralPayment($request)
    {
        $detail = $request->detail;
        $error = 0;
        $j = 0;
        foreach ($detail as $datos) {
            $id_persona = $datos;

            $rows = DB::table('plla_listado_general_pago')
                ->where('id_persona', $id_persona)
                ->where('id_tipo_pago_mensual', $request->id_tipo_pago_mensual)
                ->where('id_entidad', $request->id_entidad)
                ->delete();

            if ($rows <= 0) {
                $error++;
            }
            $j++;
        }

        if ($j == 0) {
            $response = [
                'success' => false,
                'message' => 'No hay registros para eliminar',
            ];
        } else {
            if ($error == 0) {
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
        }
        return $response;
    }
    public static function ListGeneralMonthPayment($id_tipo_pago_mensual, $id_entidad, $id_depto, $id_anho, $id_mes)
    {

        $data = DB::table('plla_listado_mensual_pago as a')
            ->join('moises.vw_trabajador as t', 't.id_persona', '=', 'a.id_persona')
            ->join('org_sede_area sa', 'sa.id_sedearea', '=', 't.id_sedearea')
            ->join('org_area ar', 'ar.id_area', '=', 'sa.id_area')
            ->join('moises.condicion_laboral as c', 'c.id_condicion_laboral', '=', 't.id_condicion_laboral')
            ->join('moises.situacion_trabajador as st', 'st.id_situacion_trabajador', '=', 't.id_situacion_trabajador')
            ->where('a.id_entidad', $id_entidad)
            ->where('a.id_anho', $id_anho)
            ->where('a.id_mes', $id_mes)
            ->where('a.id_tipo_pago_mensual', $id_tipo_pago_mensual)
            ->whereraw("sa.id_depto like '" . $id_depto . "%'")
            ->select(
                'a.id_tipo_pago_mensual',
                'a.id_entidad',
                'a.id_persona',
                'a.id_tipo_kilometraje',
                'a.sustento',
                'a.imp_kilometraje',
                'a.imp_combustible',
                'a.importe',
                'c.nombre as condicion_laboral',
                'st.nombre_corto as situacion_trabajador',
                'ar.nombre as area',
                'c.id_condicion_laboral',
                't.id_situacion_trabajador',
                'a.consumo_comb',
                'a.mantenimiento',
                'a.seguro',
                'a.depreciacion',
                DB::raw("t.paterno||' '||t.materno||' '||t.nombre as nombrecompleto, '' as eliminar")
            )
            ->orderBy('nombrecompleto', 'asc')
            ->get();
        return $data;
    }

    public static function addMonthPayment($request)
    {

        $detail = $request->detail;



        foreach ($detail as $datos) {
            $items = (object)$datos;

            DB::table('plla_listado_mensual_pago')->insert(
                [
                    'id_tipo_pago_mensual' => $request->id_tipo_pago_mensual,
                    'id_entidad' => $request->id_entidad,
                    'id_persona' => $items->id_persona,
                    'id_anho' => $request->id_anho,
                    'id_mes' => $request->id_mes,
                    'id_tipo_kilometraje' => $items->id_tipo_kilometraje,
                    'sustento' => $items->sustento,
                    'imp_kilometraje' => $items->imp_kilometraje,
                    'imp_combustible' => $items->imp_combustible,
                    'importe' => $items->importe,
                    'consumo_comb' => $items->consumo_comb,
                    'mantenimiento' => $items->mantenimiento,
                    'seguro' => $items->seguro,
                    'depreciacion' => $items->depreciacion
                ]
            );
        }
    }
    public static function DeleteMonthPayment($request)
    {
        $detail = $request->detail;
        $error = 0;
        $j = 0;
        foreach ($detail as $datos) {
            $id_persona = $datos;

            $rows = DB::table('plla_listado_mensual_pago')
                ->where('id_persona', $id_persona)
                ->where('id_tipo_pago_mensual', $request->id_tipo_pago_mensual)
                ->where('id_entidad', $request->id_entidad)
                ->where('id_anho', $request->id_anho)
                ->where('id_mes', $request->id_mes)
                ->delete();

            if ($rows <= 0) {
                $error++;
            }
            $j++;
        }

        if ($j == 0) {
            $response = [
                'success' => false,
                'message' => 'No hay registros para eliminar',
            ];
        } else {
            if ($error == 0) {
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
        }
        return $response;
    }
    public static  function listTypePayMonth($id_entidad, $nombre, $per_page)
    {
        $query = DB::table('plla_tipo_pago_mensual as a')
            ->join('plla_concepto_planilla as b', 'a.id_concepto_planilla', '=', 'b.id_concepto_planilla')
            ->join('plla_tipo_concepto as c', 'b.id_tipo_concepto', '=', 'c.id_tipo_concepto')
            ->where("a.id_entidad", '=', $id_entidad)
            ->whereraw(ComunData::fnBuscar('a.nombre') . ' like ' . ComunData::fnBuscar("'%" . $nombre . "%'"))
            ->select(
                'a.id_tipo_pago_mensual',
                'a.id_entidad',
                'a.id_concepto_planilla',
                'a.nombre',
                'a.consumo_comb',
                'a.mantenimiento',
                'a.seguro',
                'a.depreciacion',
                'a.combustible',
                DB::raw("(case when a.tipo = 'I' then 'IMPORTE' when a.tipo = 'P' then 'PORCENTAJE' when a.tipo = 'CG' then 'CALCULO GENERAL' else '' end) as tipo"),
                'c.id_tipo_concepto',
                'b.nombre as nombre_concepto',
                'c.nombre as nombre_tipo'
            )
            ->orderBy('c.nombre', 'a.nombre', 'asc')
            // ->get();
            ->paginate((int)$per_page);
        return $query;
    }
    public static function addTypePayMonth($request)
    {

        // dd($request);
        $id_entidad            =         $request->id_entidad;
        $nombre                =         $request->nombre;
        $vigencia              =         $request->vigencia;
        $id_concepto_planilla  =         $request->id_concepto_planilla;
        $tipo                  =         $request->tipo;
        $consumo_comb          =         $request->consumo_comb;
        $mantenimiento         =         $request->mantenimiento;
        $seguro                =         $request->seguro;
        $depreciacion          =         $request->depreciacion;
        $combustible           =         $request->combustible;

        $id_tipo_pago_mensual =  ComunData::correlativo('plla_tipo_pago_mensual', 'id_tipo_pago_mensual');
        if ($id_tipo_pago_mensual > 0) {
            $save = DB::table('plla_tipo_pago_mensual')->insert(
                [
                    'id_tipo_pago_mensual'  =>  $id_tipo_pago_mensual,
                    'id_entidad'            => $id_entidad,
                    'nombre'                => $nombre,
                    'vigencia'              => $vigencia,
                    'id_concepto_planilla'  => $id_concepto_planilla,
                    'tipo'                  => $tipo,
                    'consumo_comb'          => $consumo_comb,
                    'mantenimiento'         => $mantenimiento,
                    'seguro'                => $seguro,
                    'depreciacion'          => $depreciacion,
                    'combustible'           => $combustible,
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
        return $response;
    }
    public static function deleteTypePayMonth($id_tipo_pago_mensual)
    {
        $query = DB::table('plla_tipo_pago_mensual')->where('id_tipo_pago_mensual', $id_tipo_pago_mensual)->delete();
        return $query;
    }
    public static  function showTypePayMonth($id_tipo_pago_mensual)
    {
        $query = DB::table('plla_tipo_pago_mensual as a')
            ->leftjoin('plla_concepto_planilla as b', 'a.id_concepto_planilla', '=', 'b.id_concepto_planilla')
            ->where("a.id_tipo_pago_mensual", '=', $id_tipo_pago_mensual)
            ->select(
                'a.id_entidad',
                'a.nombre',
                'a.vigencia',
                'a.id_concepto_planilla',
                'b.nombre as nombre_concepto',
                'b.codigo',
                'a.tipo',
                'a.consumo_comb',
                'a.mantenimiento',
                'a.seguro',
                'a.depreciacion',
                'a.combustible'
            )
            ->get()->shift();
        return $query;
    }
    public static function updateTypePayMonth($id_tipo_pago_mensual, $request)
    {
        $id_entidad            =         $request->id_entidad;
        $nombre                =         $request->nombre;
        $vigencia              =         $request->vigencia;
        $id_concepto_planilla  =         $request->id_concepto_planilla;
        $tipo                  =         $request->tipo;
        $consumo_comb          =         $request->consumo_comb;
        $mantenimiento         =         $request->mantenimiento;
        $seguro                =         $request->seguro;
        $depreciacion          =         $request->depreciacion;
        $combustible           =         $request->combustible;
        $result = DB::table('plla_tipo_pago_mensual')
            ->where('id_tipo_pago_mensual', $id_tipo_pago_mensual)
            ->update([
                'id_entidad'            => $id_entidad,
                'nombre'                => $nombre,
                'vigencia'              => $vigencia,
                'id_concepto_planilla'  => $id_concepto_planilla,
                'tipo'                  => $tipo,
                'consumo_comb'          => $consumo_comb,
                'mantenimiento'         => $mantenimiento,
                'seguro'                => $seguro,
                'depreciacion'          => $depreciacion,
                'combustible'           => $combustible,
            ]);
        // dd($result);
    }
    public static function addPayprolControl($id_persona, $fecha, $request)
    {

        $id_entidad              =         $request->id_entidad;
        $id_depto                =         $request->id_depto;
        $id_anho                 =         $request->id_anho;
        $id_mes                  =         $request->id_mes;
        $id_estado_planilla      =         $request->id_estado_planilla;
        $id_planilla_entidad     =         $request->id_planilla_entidad;
        $id_tipo_periodo_bs      =         $request->id_tipo_periodo_bs;

        //  dd($vigencia);
        $count = DB::table('plla_proc_planilla')
            ->where('id_mes', $id_mes)
            ->where('id_planilla_entidad', $id_planilla_entidad)
            ->where('id_tipo_periodo_bs', $id_tipo_periodo_bs)
            ->count();

        if ($count == 0) {

            $id_proc_planilla =  ComunData::correlativo('plla_proc_planilla', 'id_proc_planilla');
            if ($id_proc_planilla > 0) {
                $save = DB::table('plla_proc_planilla')->insert(
                    [
                        'id_proc_planilla'   =>  $id_proc_planilla,
                        'id_entidad'               => $id_entidad,
                        'id_depto'               => $id_depto,
                        'id_anho'                => $id_anho,
                        'id_mes'                 => $id_mes,
                        'id_estado_planilla'     => $id_estado_planilla,
                        'id_planilla_entidad'    => $id_planilla_entidad,
                        // 'vigencia'               => $vigencia,
                        'id_persona'             => $id_persona,
                        'fec_proceso'            => $fecha,
                        'id_tipo_periodo_bs'     => $id_tipo_periodo_bs,
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

    public static  function listPayprolControl($id_entidad, $id_depto, $id_anho, $id_mes)
    {
        $query = DB::table('plla_proc_planilla as a')
            ->join('plla_planilla_entidad as b', 'a.id_planilla_entidad', '=', 'b.id_planilla_entidad')
            ->join('plla_estado_planilla as c', 'a.id_estado_planilla', '=', 'c.id_estado_planilla')
            ->join('plla_grupo_planilla as d', 'b.id_grupo_planilla', '=', 'd.id_grupo_planilla')
            ->join('plla_tipo_planilla as e', 'b.id_tipo_planilla', '=', 'e.id_tipo_planilla')
            ->join('users as f', 'a.id_persona', '=', 'f.id')
            ->leftjoin('plla_tipo_periodo_bs as g', 'a.id_tipo_periodo_bs', '=', 'g.id_tipo_periodo_bs')
            ->where("a.id_entidad", '=', $id_entidad)
            ->where("a.id_depto", '=', $id_depto)
            ->where("a.id_anho", '=', $id_anho)
            ->where("a.id_mes", '=', $id_mes)
            ->select(
                'a.id_proc_planilla',
                'a.id_entidad',
                'a.id_depto',
                'a.id_anho',
                'a.id_mes',
                'c.nombre as estado',
                'a.id_estado_planilla',
                'd.nombre as nombre_grupo',
                'e.nombre as nombre_tipo',
                'a.fec_proceso as fecha',
                'f.email as user',
                'g.nombre as nombre_tipo_periodo_bs'
            )
            ->orderBy('a.id_proc_planilla', 'desc')
            ->get();
        return $query;
    }

    public static function deletePayprolControl($id_proc_planilla)
    {
        $query = DB::table('plla_proc_planilla')->where('id_proc_planilla', $id_proc_planilla)->delete();
        return $query;
    }


    public static function  updatePayprolControl($id_proc_planilla, $request)
    {
        // dd($request->id_estado_planilla);

        $result = DB::table('plla_proc_planilla')
            ->where('id_proc_planilla', $id_proc_planilla)
            ->update([
                'id_estado_planilla' => $request->id_estado_planilla,
            ]);

        if ($result > 0) {
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
    public static function  updatePayprolControlClose($id_proc_planilla, $request)
    {
        // dd($request->id_estado_planilla);

        $result = DB::table('plla_proc_planilla')
            ->where('id_proc_planilla', $id_proc_planilla)
            ->update([
                'id_estado_planilla' => $request->id_estado_planilla,
                'comentario' => $request->comentario,
            ]);

        if ($result > 0) {
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


    public static function regSalaryScaleTeacher($request)
    {
        $response = [];
        $respose_boolean = false;
        try {
            $rangos = json_decode(json_encode($request->rangos), true);
            foreach ($rangos as $datos) {
                $id_escala_sala_docente = '';
                if ($datos['type'] == 'c') {
                    $id_escala_sala_docente = ComunData::correlativo('plla_escala_sala_docente', 'id_escala_sala_docente');
                } else {
                    $id_escala_sala_docente = $datos['id_escala_sala_docente'];
                }
                $sql = DB::table('plla_escala_sala_docente');
                $data = [
                    'id_escala_sala_docente' => $id_escala_sala_docente,
                    'id_entidad' => $datos['id_entidad'],
                    'id_depto' => $datos['id_depto'],
                    'id_nivel_ensenanza' => $datos['id_nivel_ensenanza'],
                    'id_modalidad_estudio' => $datos['id_modalidad_estudio'],
                    'id_situacion_educativo' => $datos['id_situacion_educativo'],
                    'id_exp_laboral' => $datos['id_exp_laboral'],
                    'ht_desde' => (isset($datos['ht_desde'])) ? $datos['ht_desde'] : '',
                    'ht_hasta' => (isset($datos['ht_hasta'])) ? $datos['ht_hasta'] : '',
                    'hp_desde' => (isset($datos['hp_desde'])) ? $datos['hp_desde'] : '',
                    'hp_hasta' => (isset($datos['hp_hasta'])) ? $datos['hp_hasta'] : '',
                    'sup_desde' => (isset($datos['sup_desde'])) ? $datos['sup_desde'] : '',
                    'sup_hasta' => (isset($datos['sup_hasta'])) ? $datos['sup_hasta'] : '',
                    'vigencia' => (isset($datos['vigencia'])) ? $datos['vigencia'] : '',
                ];
                if ($datos['type'] == 'c') {
                    $sql->insert($data);
                } else {
                    $sql->where('id_escala_sala_docente', $id_escala_sala_docente);
                    $sql->update($data);
                }
                //$response['as'] = (isset($datos['ht_desde']))?'a':'b';
            }
            $response['message'] = 'as';
            $response['success'] = true;
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
            $response['success'] = false;
        }
        return $response;
    }

    public static function getSalaryScaleTeacher($request)
    {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_nivel_ensenanza = $request->id_nivel_ensenanza;
        $id_modalidad_estudio = $request->id_modalidad_estudio;
        $id_situacion_educativo = $request->id_situacion_educativo;
        $id_exp_laboral = $request->id_exp_laboral;
        $sql = DB::table('PLLA_ESCALA_SALA_DOCENTE e');
        $sql->join('DAVID.TIPO_NIVEL_ENSENANZA a', 'a.ID_NIVEL_ENSENANZA', '=', 'e.ID_NIVEL_ENSENANZA');
        $sql->join('DAVID.TIPO_MODALIDAD_ESTUDIO b', 'b.ID_MODALIDAD_ESTUDIO', '=', 'e.ID_MODALIDAD_ESTUDIO');
        $sql->join('plla_exp_laboral ex', 'ex.id_exp_laboral', '=', 'e.id_exp_laboral');
        $sql->join('MOISES.situacion_educativa s', 's.id_situacion_educativo', '=', 'e.id_situacion_educativo');
        $sql->join('conta_entidad_depto d', 'd.id_depto', '=', 'e.id_depto');
        $sql->join('conta_entidad en', 'en.id_entidad', '=', 'e.id_entidad');
        $sql->where('e.id_entidad', '=', $id_entidad);
        $sql->where("e.id_depto", '=', $id_depto);
        if ($id_nivel_ensenanza && $id_nivel_ensenanza != '') {
            $sql->where("e.id_nivel_ensenanza", '=', $id_nivel_ensenanza);
        }
        if ($id_modalidad_estudio && $id_modalidad_estudio != '') {
            $sql->where("e.id_modalidad_estudio", '=', $id_modalidad_estudio);
        }
        if ($id_situacion_educativo && $id_situacion_educativo != '') {
            $sql->where("e.id_situacion_educativo", '=', $id_situacion_educativo);
        }
        if ($id_exp_laboral && $id_exp_laboral != '') {
            $sql->where("e.id_exp_laboral", '=', $id_exp_laboral);
        }
        $sql->select(
            'e.*',
            'b.nombre as MODALIDAD_ESTUDIO',
            'a.nombre as NIVEL_ENSENANZA',
            'ex.nombre as EXPERIENCIA',
            's.nombre as GRADO',
            'd.nombre as DEPARTAMENTO',
            'en.nombre as ENTIDAD'
        );
        $sql->orderByRaw('e.id_nivel_ensenanza,e.id_modalidad_estudio, e.id_situacion_educativo');
        $data = $sql->get();
        return $data;

        return $data;
    }

    public static function getSalaryScaleTeacherSp($request)
    {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_nivel_ensenanza = $request->id_nivel_ensenanza;
        $id_modalidad_estudio = $request->id_modalidad_estudio;
        $id_situacion_educativo = $request->id_situacion_educativo;
        $id_exp_laboral = $request->id_exp_laboral;
        $sql = DB::table('PLLA_ESCALA_SALA_DOCENTE e');
        $sql->join('DAVID.TIPO_NIVEL_ENSENANZA a', 'a.ID_NIVEL_ENSENANZA', '=', 'e.ID_NIVEL_ENSENANZA');
        $sql->join('DAVID.TIPO_MODALIDAD_ESTUDIO b', 'b.ID_MODALIDAD_ESTUDIO', '=', 'e.ID_MODALIDAD_ESTUDIO');
        $sql->join('plla_exp_laboral ex', 'ex.id_exp_laboral', '=', 'e.id_exp_laboral');
        $sql->join('MOISES.situacion_educativa s', 's.id_situacion_educativo', '=', 'e.id_situacion_educativo');
        $sql->join('conta_entidad_depto d', 'd.id_depto', '=', 'e.id_depto');
        $sql->join('conta_entidad en', 'en.id_entidad', '=', 'e.id_entidad');
        $sql->where('e.id_entidad', '=', $id_entidad);
        $sql->where("e.id_depto", '=', $id_depto);
        $sql->where("e.vigencia", '=', '1');
        if ($id_nivel_ensenanza && $id_nivel_ensenanza != '') {
            $sql->where("e.id_nivel_ensenanza", '=', $id_nivel_ensenanza);
        }
        if ($id_modalidad_estudio && $id_modalidad_estudio != '') {
            $sql->where("e.id_modalidad_estudio", '=', $id_modalidad_estudio);
        }
        if ($id_situacion_educativo && $id_situacion_educativo != '') {
            $sql->where("e.id_situacion_educativo", '=', $id_situacion_educativo);
        }
        if ($id_exp_laboral && $id_exp_laboral != '') {
            $sql->where("e.id_exp_laboral", '=', $id_exp_laboral);
        }
        $sql->select(
            'e.*',
            'b.nombre as MODALIDAD_ESTUDIO',
            'a.nombre as NIVEL_ENSENANZA',
            'ex.nombre as EXPERIENCIA',
            's.nombre as GRADO',
            'd.nombre as DEPARTAMENTO',
            'en.nombre as ENTIDAD'
        );
        $sql->orderByRaw('e.id_nivel_ensenanza,e.id_modalidad_estudio, e.id_situacion_educativo');
        $data = $sql->get()->first();
        return $data;
    }

    public static function getSemestrePrograma($request)
    {
        $id_persona = $request->id_persona;
        $query =    "select pe.nombre as escuela,pe.id_programa_estudio,tn.nombre as nivel,tn.id_nivel_ensenanza,me.nombre as modalidad,me.id_modalidad_estudio
        from david.acad_carga_curso_docente cd
        join david.acad_carga_curso cc on cc.id_carga_curso=cd.id_carga_curso
        join david.acad_semestre_programa sp on sp.id_semestre_programa=cc.id_semestre_programa
        join david.acad_programa_estudio pe on pe.id_programa_estudio=sp.id_programa_estudio
        join david.tipo_nivel_ensenanza tn on tn.id_nivel_ensenanza=pe.id_nivel_ensenanza
        join DAVID.tipo_modalidad_estudio me on me.id_modalidad_estudio=pe.id_modalidad_estudio
        where cd.id_persona =" . $id_persona . " 
        group by pe.nombre,pe.id_programa_estudio,tn.nombre,tn.id_nivel_ensenanza,me.nombre,me.id_modalidad_estudio";
        $sql = collect(DB::select($query));

        $id_exp_laboral = $request->id_exp_laboral;
        $id_situacion_educativo = $request->id_situacion_educativo;
        $id_depto = $request->id_depto;
        $id_entidad = $request->id_entidad;
        if (sizeof($sql) > 0) {
            for ($i = 0; $i < sizeof($sql); $i++) {
                $id_nivel_ensenanza = $sql[$i]->id_nivel_ensenanza;
                $id_modalidad_estudio = $sql[$i]->id_modalidad_estudio;
                $sql[$i]->escala = PayrollData::getEscalasBySemestre(
                    $id_nivel_ensenanza,
                    $id_modalidad_estudio,
                    $id_exp_laboral,
                    $id_situacion_educativo,
                    $id_depto,
                    $id_entidad
                );
            }
        }
        return $sql;
    }

    public static function getEscalasBySemestre($id_nivel_ensenanza, $id_modalidad_estudio, $id_exp_laboral, $id_situacion_educativo, $id_depto, $id_entidad)
    {
        $query =    "select  p.nombre as experiencia,s.nombre as grado,e.*  
        from plla_escala_sala_docente e,plla_exp_laboral p,moises.situacion_educativa s 
        where e.id_nivel_ensenanza=" . $id_nivel_ensenanza . " and e.id_modalidad_estudio=" . $id_modalidad_estudio . " 
        and e.id_exp_laboral=" . $id_exp_laboral . " and e.id_situacion_educativo=" . $id_situacion_educativo . " 
        and e.id_depto = " . $id_depto . " and e.id_entidad =" . $id_entidad . " and e.vigencia=1 
        and e.id_exp_laboral=p.id_exp_laboral and s.id_situacion_educativo=e.id_situacion_educativo";
        return collect(DB::select($query))->first();
    }

    public static function getEscala($id_escala)
    {
        $query =    "select  p.nombre as experiencia,s.nombre as grado,e.*  
        from plla_escala_sala_docente e,plla_exp_laboral p,moises.situacion_educativa s 
        where e.id_escala_sala_docente=" . $id_escala . " 
        and e.id_exp_laboral=p.id_exp_laboral and s.id_situacion_educativo=e.id_situacion_educativo";
        //and e.vigencia=1 
        return collect(DB::select($query))->first();
    }

    public static function getCostAssigned($request)
    {
        $id_persona = $request->id_persona;
        $query =    "select c.*,p.nombre as escuela,tn.nombre as nivel,tn.id_nivel_ensenanza, me.nombre as modalidad,me.id_modalidad_estudio,'0' as changes
        from plla_docente_costoxhora c,david.acad_programa_estudio p,david.tipo_nivel_ensenanza tn,david.tipo_modalidad_estudio me
        where c.id_programa_estudio=p.id_programa_estudio
        and tn.id_nivel_ensenanza=p.id_nivel_ensenanza
        and me.id_modalidad_estudio = p.id_modalidad_estudio
        and id_persona =" . $id_persona . " order by c.vigencia desc";
        $sql = collect(DB::select($query));
        if (sizeof($sql) > 0) {
            for ($i = 0; $i < sizeof($sql); $i++) {
                $id_escala = $sql[$i]->id_escala_sala_docente;
                $sql[$i]->escala = PayrollData::getEscala($id_escala);
            }
        }
        return $sql;
    }

    //reg_cost_x_hora

    public static function regCostxHour($data, $id_user)
    {
        try {
            $response = [];
            $toInsert = json_decode($data, true);
            $id_docente_costoxhora = ComunData::correlativo('plla_docente_costoxhora', 'id_docente_costoxhora');
            $toInsert['id_docente_costoxhora'] = $id_docente_costoxhora;
            $toInsert['id_user_reg'] = $id_user;
            $toInsert['fecha_reg'] = new DateTime();
            $affected = DB::table('plla_docente_costoxhora')->insert($toInsert);
            if ($affected) {
                $response = [
                    'success' => true,
                    'id_docente_costoxhora' => $id_docente_costoxhora,
                    'message' => 'Creado'
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

    public static function updCostxHour($id_docente_costoxhora, $data, $id_user)
    {
        $response = [];
        $toUpdate = json_decode($data, true);
        $toUpdate['id_user_mod'] = $id_user;
        $toUpdate['fecha_mod'] = new DateTime();
        $affected = DB::table('plla_docente_costoxhora')
            ->where('id_docente_costoxhora', $id_docente_costoxhora)
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
}
