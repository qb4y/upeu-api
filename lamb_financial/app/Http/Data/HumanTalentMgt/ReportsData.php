<?php
namespace App\Http\Data\HumanTalentMgt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\HumanTalentMgt\ComunData;
use PDO;
class ReportsData extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function reportRegisterFirm($request, $id_user)
    {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_periodo_vac = $request->id_periodo_vac;
        $id_area = $request->id_area;
        $persona =  $request->persona;
        $per_page =  $request->per_page;

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
        // $estado =  $request->estado;
        $q = DB::table('moises.vw_trabajador as a');
            $q->leftJoin('eliseo.plla_periodo_vac_trab as b', 'a.id_persona', '=', DB::raw("b.id_persona and b.id_periodo_vac = ".$id_periodo_vac.""));
            $q->join('eliseo.plla_estado_vac_trab as vt', 'b.id_estado_vac_trab', '=', 'vt.id_estado_vac_trab');
            $q->join('org_sede_area c', 'a.id_sedearea', '=', 'c.id_sedearea');
            $q->join('org_area d', 'c.id_area', '=', 'd.id_area');
            $q->where('c.id_entidad', $id_entidad);
            $q->whereraw("c.id_depto like '".$id_depto."%'");
            if (strlen($id_area)>0) {
                $q->where('c.id_area', $id_area);
            }
            if ($restringido == 'S') {
                if ($objet->id_tipo_nivel_vista == 4) {
                    $q->whereIn('c.id_area', $ids);
                }
                if ($objet->id_tipo_nivel_vista == 5) {
                    $q->where('a.id_persona', $id_user); 
                }
            } 
            if ($restringido == 'U') {
                $q->where('a.id_persona', $id_user); 
            }
            if (strlen($persona)>0) {
                $q->whereraw("(upper(a.nombre) like upper('%".$persona."%')
                                or upper(a.nombre ||' ' || a.paterno ) like upper('%".$persona."%')
                                or upper(a.paterno ||' ' || a.materno ) like upper('%".$persona."%')
                                or a.num_documento like '%".$persona."%')");
            }
            // if (strlen($estado)>0) {
            //     $q->where('b.estado', $estado);
            // }
            $q->select(
                'a.id_persona',
                DB::raw("(a.nombre || ' ' || a.paterno || ' ' || a.materno ) as nombres"),
                'a.fecha_inicio',
                'a.fecha_fin_previsto',
                'b.id_periodo_vac_trab',
                'b.periodo_ini',
                'b.periodo_fin',
                'b.total_dias',
                'vt.nombre as estado',
                'b.total_dias_efect',
                'a.num_documento',
                'd.nombre as nombre_area',
                'b.id_estado_vac_trab'
            );
            $q->orderBy('nombres', 'asc');
            $query = $q->paginate((int)$per_page);

            // dd($query);
            $data = array();
            foreach ($query as $row) {
            $item = array();
            $item['id_persona'] = $row->id_persona;
            $item['nombres'] = $row->nombres;
            $item['num_documento'] = $row->num_documento;
            $item['nombre_area'] = $row->nombre_area;
            $item['fecha_inicio'] = $row->fecha_inicio;
            $item['fecha_fin_previsto'] = $row->fecha_fin_previsto;
            $item['periodo_ini'] = $row->periodo_ini;
            $item['periodo_fin'] = $row->periodo_fin;
            $item['total_dias'] = $row->total_dias;
            $item['total_dias_efect'] = $row->total_dias_efect;
            $item['id_periodo_vac_trab'] = $row->id_periodo_vac_trab;
            $item['estado'] = $row->estado;
            $item['id_estado_vac_trab'] = $row->id_estado_vac_trab;
            $item['details'] = ReportsData::reportsChild($row->id_periodo_vac_trab);
            $data[] = $item;
        }
        $que = ['data1' =>  $query, 'data2'=> $data];
        return $que;
    }
    public static function reportsChild($id_periodo_vac_trab) {
        $query = DB::table('plla_rol_vacacional as a')
            ->join('eliseo.plla_estado_rol_vac as c', 'a.id_estado_rol_vac', '=', 'c.id_estado_rol_vac')
            ->join('users as b', 'a.id_user_reg', '=', 'b.id')
            ->where('a.id_periodo_vac_trab', $id_periodo_vac_trab)
            ->select(
                'a.id_rol_vacacion',
                DB::raw("to_char(a.fecha_ini, 'YYYY-MM-DD') as fecha_ini"),
                DB::raw("to_char(a.fecha_fin, 'YYYY-MM-DD') as fecha_fin"),
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
                'a.dias_efect'
            )
            ->orderBy('orden', 'asc')
            ->orderBy('a.fecha_ini', 'asc')
            ->get();
        return $query;
    }

  
  public static function reportOuthinMonth($request, $id_user) {
    $id_entidad = $request->id_entidad;
    $id_depto = $request->id_depto;
    $yearMonth = $request->year_mes;
    $id_area = $request->id_area;
    $persona =  $request->persona;
    $per_page =  $request->per_page;

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

  $q = DB::table('eliseo.plla_rol_vacacional as a');
             $q->join('eliseo.plla_periodo_vac_trab as b', 'a.id_periodo_vac_trab', '=', 'b.id_periodo_vac_trab');
             $q->join('eliseo.plla_estado_vac_trab vt', 'b.id_estado_vac_trab', '=', 'vt.id_estado_vac_trab');
             $q->join('moises.vw_trabajador as c', 'b.id_persona', '=', 'c.id_persona');
             $q->join('eliseo.org_sede_area as d', 'c.id_sedearea', '=', 'd.id_sedearea');
             $q->join('eliseo.org_area as e', 'd.id_area', '=', 'e.id_area');
             $q->leftJoin('eliseo.plla_puesto pue', 'c.id_puesto', '=', 'pue.id_puesto');
             $q->where('d.id_entidad', $id_entidad);
             $q->whereraw("d.id_depto like '".$id_depto."%'");
             $q->where('a.condicion', '=', 'P');
             $q->whereraw("(to_char(a.fecha_ini, 'YYYY-MM')='".$yearMonth."' or to_char(a.fecha_fin, 'YYYY-MM')='".$yearMonth."')");
             if (strlen($id_area)>0) {
              $q->where('d.id_area', $id_area);
              }
              if ($restringido == 'S') {
                if ($objet->id_tipo_nivel_vista == 4) {
                    $q->whereIn('d.id_area', $ids);
                }
                if ($objet->id_tipo_nivel_vista == 5) {
                    $q->where('b.id_persona', $id_user); 
                }
            } 
            if ($restringido == 'U') {
                $q->where('a.id_persona', $id_user); 
            }
              if (strlen($persona)>0) {
                  $q->whereraw("(upper(c.nombre) like upper('%".$persona."%')
                                  or upper(c.nombre ||' ' || c.paterno ) like upper('%".$persona."%')
                                  or upper(c.paterno ||' ' || c.materno ) like upper('%".$persona."%')
                                  or c.num_documento like '%".$persona."%')");
              }
              $q->select('a.id_periodo_vac_trab', 'b.id_persona',  'pue.nombre as nombre_puesto', 
              DB::raw("(c.nombre|| ' ' ||c.paterno|| ' ' ||c.materno) as nombres"), 'e.nombre as nombre_area',
               'c.num_documento', 'vt.nombre as estado', 'b.id_estado_vac_trab');
              $q->orderBy('nombres');
              $q->groupBy('a.id_periodo_vac_trab', 'b.id_persona',  'pue.nombre',  DB::raw("(c.nombre|| ' ' ||c.paterno|| ' ' ||c.materno)"), 
              'e.nombre', 'c.num_documento', 'vt.nombre', 'b.id_estado_vac_trab');
              $query = $q->paginate((int)$per_page);
              $datar = array();
              foreach ($query as $cabece) {
                  array_push($datar, [
                    'id_periodo_vac_trab' => $cabece->id_periodo_vac_trab,
                    'nombres' => $cabece->nombres,
                    'nombre_puesto' => $cabece->nombre_puesto,
                    'id_persona' => $cabece->id_persona,
                    'nombre_area' => $cabece->nombre_area,
                    'estado' => $cabece->estado,
                    'id_estado_vac_trab' => $cabece->id_estado_vac_trab,
                    'num_documento' => $cabece->num_documento,
                    'details' => ReportsData::childss($cabece->id_periodo_vac_trab, $yearMonth),
                    ]
                  );
              }
              $que = ['data1' =>  $query, 'data2'=> $datar];
              return $que;
            
             
}
public static function childss($id_periodo_vac_trab, $yearMonth) {
  // dd($id_periodo_vac_trab, $yearMonth);
  $query = DB::table('plla_rol_vacacional as a')
      ->join('eliseo.plla_estado_rol_vac as c', 'a.id_estado_rol_vac', '=', 'c.id_estado_rol_vac')
      ->where('a.id_periodo_vac_trab', $id_periodo_vac_trab)
      ->whereraw("(to_char(a.fecha_ini, 'YYYY-MM')='".$yearMonth."' or to_char(a.fecha_fin, 'YYYY-MM')='".$yearMonth."')")
      ->select(
          'a.id_rol_vacacion',
          DB::raw("to_char(a.fecha_ini, 'YYYY-MM-DD') as fecha_ini"),
          DB::raw("to_char(a.fecha_fin, 'YYYY-MM-DD') as fecha_fin"),
          'a.dias',
          'a.condicion',
          'c.nombre as estado',
          'a.id_parent',
          'a.fecha_registra',
          'a.id_user_reg',
          'a.confirmacion_salida',
          'a.confirmacion_retorno',
          'a.dias_efect'
      )
      ->orderBy('a.fecha_ini', 'asc')
      ->get();
  return $query;
}
public static function calendarHolidays($request, $id_user) {
  $id_entidad = $request->id_entidad;
  $id_depto = $request->id_depto;
  $id_periodo_vac = $request->id_periodo_vac;
  $id_area = $request->id_area;
  $persona =  $request->persona;
  $estado =  $request->estado;
//   dd($id_entidad, $id_depto, $id_periodo_vac, $id_sedearea, $persona);
  $nerror = 0;
  $msgerror = '';
  for ($i = 1; $i <= 200; $i++) {
      $msgerror .= '';
  }
  if(strlen($id_area)==0){
      $id_area=0;
    }
  $pdo = DB::getPdo();
  $stmt = $pdo->prepare("BEGIN PKG_HUMAN_TALENT_MGT.SP_GENERAR_REPVAC(
                          :P_ID_ENTIDAD,
                          :P_ID_DEPTO,
                          :P_ID_PERIODO_VAC,
                          :P_ID_AREA,
                          :P_DATO,
                          :P_ESTADO,
                          :P_ID_USER,
                          :P_ERROR,
                          :P_MSGERROR
                        ); end;");
  $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
  $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
  $stmt->bindParam(':P_ID_PERIODO_VAC', $id_periodo_vac, PDO::PARAM_INT);
  $stmt->bindParam(':P_ID_AREA', $id_area, PDO::PARAM_INT);
  $stmt->bindParam(':P_DATO', $persona, PDO::PARAM_STR);
  $stmt->bindParam(':P_ESTADO', $estado, PDO::PARAM_STR);
  $stmt->bindParam(':P_ID_USER', $id_user, PDO::PARAM_INT);
  $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
  $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
  $stmt->execute();
  $return = [
      'nerror' => $nerror,
      'msgerror' => $msgerror,
  ];
  return $return;
  }
  public static function calendarHeaders($id_user) {
        $query = DB::table('eliseo.plla_rep_vacacion as a')
            ->select('a.anho', 'a.mes')
            ->where('a.id_user', $id_user)
            ->groupBy('a.anho', 'a.mes')
            ->orderBy('a.anho')
            ->orderBy('a.mes')
            ->get();
        $data = array();
        foreach ($query as $row) {
        $item = array();
        $item['anho'] = $row->anho;
        $item['mes'] = $row->mes;
        $item['nombre_mes'] = ReportsData::nombreMes($row->mes);
        $item['dias'] = ReportsData::calendarHeadersChild($row->anho, $row->mes, $id_user);
        $data[] = $item;
        }
        return $data;
    }
  private static function calendarHeadersChild($anho, $mes,  $id_user) {
        $query = DB::table('eliseo.plla_rep_vacacion as a')
            ->where('a.id_user', $id_user)
            ->where('a.anho', $anho)
            ->where('a.mes', $mes)
            ->select('a.dia', 'a.nom_dias')
            ->groupBy('a.dia', 'a.nom_dias')
            ->orderBy('a.dia')
            ->get();
        return $query;
  }
  private static function nombreMes($mes) {
            $mes = DB::table('eliseo.conta_mes as a')
            ->where('a.id_mes', $mes)
            ->select('a.nombre')
            ->first();
            $nombre_mes = $mes->nombre;
        return $nombre_mes;
    }
    public static function calendarBody($request, $id_user) {
        $per_page = $request->per_page;
        $query = DB::table('eliseo.plla_rep_vacacion as a')
            ->select('a.id_persona', 'a.nombre_persona', 'a.area', 'a.puesto', 'a.anho', 'a.periodo')
            ->where('a.id_user', $id_user)
            ->groupBy('a.id_persona', 'a.nombre_persona', 'a.area', 'a.puesto', 'a.anho', 'a.periodo')
            ->orderBy('a.nombre_persona')
            ->paginate((int)$per_page);
        $paginado = $query;
            // ->get();
        $data = array();
        foreach ($query as $row) {
        $item = array();
        $item['id_persona'] = $row->id_persona;
        $item['nombre_persona'] = $row->nombre_persona;
        $item['area'] = $row->area;
        $item['puesto'] = $row->puesto;
        $item['periodo'] = $row->periodo;
        $item['anho'] = $row->anho;
        $item['foto'] = ReportsData::fotoTrabajador($row->id_persona) ? 'https://files-erp.upeu.edu.pe/media/'.ReportsData::fotoTrabajador($row->id_persona).'.jpg' : 'https://cdn2.iconfinder.com/data/icons/ios-7-icons/50/user_male2-512.png';
        $item['dias'] =  ReportsData::calendarBodyChild($row->id_persona, $id_user);
        $data[] = $item;
        }
        $que = ['paginate' =>  $paginado, 'data'=> $data];
        return $que;
        // return $data;
    }
    private static function calendarBodyChild($id_persona, $id_user) {
        $q = DB::table('eliseo.plla_rep_vacacion as a')
            ->where('a.id_user', $id_user)
            ->where('a.id_persona', $id_persona)
            ->select('a.vacacion')
            ->orderBy('a.anho')
            ->orderBy('a.mes')
            ->orderBy('a.dia')
            ->pluck('a.vacacion')->toArray();
       $query = implode("", $q);
        return $query;
  }
  private static function fotoTrabajador($id_persona) {
    $foto = DB::table('MOISES.PERSONA_NATURAL')
    ->where('ID_PERSONA', $id_persona)
    ->select('FOTO')
    ->first();
    $foto_trab = $foto->foto;
    return $foto_trab;
  }
}
?>