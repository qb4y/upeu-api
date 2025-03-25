<?php
namespace App\Http\Data\HumanTalentMgt;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\HumanTalentMgt\ComunData;
use PDO;

class LicensesData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function listTypeSuspension($nombre, $per_page)
    {
        $query = DB::table('plla_tipo_suspension')
            ->whereraw(ComunData::fnBuscar('nombre') . ' like ' . ComunData::fnBuscar("'%" . $nombre . "%'"))
            ->select('id_tipo_suspension', 'nombre', 'nombre_corto', 'tipo', 'codigo', 'periodo', 'codsunat', 'cant_dias', 'vigencia')
            ->orderBy('id_tipo_suspension', 'desc')
            ->paginate((int)$per_page);
        return $query;
    }
    public static function showTypeSuspension($id_tipo_suspension)
    {
        $query = DB::table('plla_tipo_suspension')
            ->where('id_tipo_suspension', $id_tipo_suspension)
            ->select('id_tipo_suspension', 'nombre', 'nombre_corto', 'tipo', 'codigo', 'periodo', 'codsunat', 'cant_dias', 'vigencia')
            ->orderBy('id_tipo_suspension', 'desc')
            ->get()->shift();
        return $query;
    }
    public static function addTypeSuspension($request)
    {
// dd('req', $request);
        $nombre = $request->nombre;
        $nombre_corto = $request->nombre_corto;
        $tipo = $request->tipo;
        $codigo = $request->codigo;
        $periodo = $request->periodo;
        $codsunat = $request->codsunat;
        $cant_dias = $request->cant_dias;
        $vigencia = $request->vigencia;
        
        $count = DB::table('plla_tipo_suspension')
            ->where('nombre', $nombre)
            ->count();

        if ($count == 0) {
            // $id_tipo_suspension = ComunData::correlativo('plla_tipo_suspension', 'cast(id_tipo_suspension as integer)');
            $id_tipo_suspension = ComunData::correlativo('plla_tipo_suspension', 'id_tipo_suspension');
            if ($id_tipo_suspension > 0) {
                $save = DB::table('plla_tipo_suspension')->insert(
                    [
                        // 'id_tipo_suspension' =>  str_pad($id_tipo_suspension,2,"0",STR_PAD_LEFT),
                        'id_tipo_suspension' => $id_tipo_suspension,
                        'nombre' => $nombre,
                        'nombre_corto' =>  $nombre_corto,
                        'tipo' =>  $tipo,
                        'codigo' => $codigo,
                        'periodo' =>  $periodo,
                        'codsunat' =>  $codsunat,
                        'cant_dias' =>  $cant_dias,
                        'vigencia' => $vigencia,

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
    public static function updateTypeSuspension($id_tipo_suspension, $request)
    {
        $nombre = $request->nombre;
        $nombre_corto = $request->nombre_corto;
        $tipo = $request->tipo;
        $codigo = $request->codigo;
        $periodo = $request->periodo;
        $codsunat = $request->codsunat;
        $cant_dias = $request->cant_dias;
        $vigencia = $request->vigencia;
        $result = DB::table('plla_tipo_suspension')
            ->where('id_tipo_suspension', $id_tipo_suspension)
            ->update([
                'nombre' => $nombre,
                'nombre_corto' =>  $nombre_corto,
                'tipo' =>  $tipo,
                'codigo' => $codigo,
                'periodo' =>  $periodo,
                'codsunat' =>  $codsunat,
                'cant_dias' =>  $cant_dias,
                'vigencia' => $vigencia,
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
    public static function deleteTypeSuspension($id_tipo_suspension)
    {
        $query = DB::table('plla_tipo_suspension')->where('id_tipo_suspension', $id_tipo_suspension)->delete();
        return $query;
    }

    public static function addRegisterLisensesPermits($id_user, $request)
    {
        // dd($request);
        $hora_inicio             = $request->hora_inicio;
        $hora_fin                = $request->hora_fin;
        $fecha_desdeD             = $request->fecha_desde;
        $fecha_hastaH            = $request->fecha_hasta;
        $periodo                 = $request->periodo;
        
        if ($periodo == 'H') {
            $fecha_desde = $fecha_desdeD. ' ' . $hora_inicio.':'.'00';
            $fecha_hasta = $fecha_desdeD. ' ' . $hora_fin.':'.'00';
        } else {
            $fecha_desde = $fecha_desdeD;
            $fecha_hasta = $fecha_hastaH;
        }
        $nerror = 0;
        $id_licencia_permiso_new = '';
        $licencia_perimso_url = '';
        $msgerror = '';
        for ($i = 1; $i <= 200; $i++) {
            $msgerror .= '0';
        }
        for ($id = 1; $id <= 25; $id++) {
            $id_licencia_permiso_new .= '0';
        }
        $id_licencia_permiso     = $request->id_licencia_permiso;
        $id_entidad              = $request->id_entidad;
        $id_persona              = $request->id_persona;
        $id_tipo_suspension      = $request->id_tipo_suspension;
        $id_sedearea             = $request->id_sedearea;
        $periodo                 = $request->periodo;
        $motivo                  = $request->motivo;
        $descripcion             = $request->descripcion;
        $enhoras                 = $request->enhoras;
        $tiempo                  = $request->tiempo;

        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_HUMAN_TALENT_MGT.SP_REGISTRAR_LICENCIA_PERMISO(
                                :P_ID_LICENCIA_PERMISO,
                                :P_ID_ENTIDAD,
                                :P_ID_PERSONA,
                                :P_ID_TIPO_SUSPENSION,
                                :P_ID_SEDEAREA,
                                :P_PERIODO,
                                :P_MOTIVO,
                                :P_DESCRIPCION,
                                :P_FECHA_DESDE,
                                :P_FECHA_HASTA,
                                :P_ID_USER_REG,
                                :P_TIEMPO,
                                :P_ENHORAS,
                                :P_ERROR,
                                :P_MSGERROR,
                                :P_ID_LICENCIA_PERMISO_NEW); end;");

        $stmt->bindParam(':P_ID_LICENCIA_PERMISO',     $id_licencia_permiso, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_ENTIDAD',              $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PERSONA',              $id_persona, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_TIPO_SUSPENSION',      $id_tipo_suspension, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_SEDEAREA',             $id_sedearea, PDO::PARAM_INT);
        $stmt->bindParam(':P_PERIODO',                 $periodo, PDO::PARAM_STR);
        $stmt->bindParam(':P_MOTIVO',                  $motivo, PDO::PARAM_STR);
        $stmt->bindParam(':P_DESCRIPCION',             $descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':P_FECHA_DESDE',             $fecha_desde, PDO::PARAM_STR);
        $stmt->bindParam(':P_FECHA_HASTA',             $fecha_hasta, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_USER_REG',             $id_user, PDO::PARAM_INT);
        $stmt->bindParam(':P_TIEMPO',                  $tiempo, PDO::PARAM_STR);
        $stmt->bindParam(':P_ENHORAS',                 $enhoras, PDO::PARAM_STR);
        $stmt->bindParam(':P_ERROR',                   $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR',                $msgerror, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_LICENCIA_PERMISO_NEW', $id_licencia_permiso_new, PDO::PARAM_STR);
        $stmt->execute();

        if($nerror==0){
            $file = $request->file('licencia_permiso_url');
            if(!empty($file)){
                $ext = $file->getClientOriginalExtension();
                $destino ='gth';//por ahora
                $uid=hash("md2",(string)microtime());
                $archivo     = $uid.'.'.strtolower($ext);
                $licencia_permiso_url = $archivo;
                $file->move($destino, $archivo); 
                //actualizar ruta
                if(!empty($licencia_permiso_url)){
                    DB::table('eliseo.plla_licencia_permiso')
                        ->where('id_licencia_permiso', $id_licencia_permiso_new)
                        ->update(['licencia_permiso_url' => $licencia_permiso_url]);
                }
            }
        }
        $return = [
            'nerror' => $nerror,
            'msgerror' => $msgerror,
            'id_licencia_permiso'=>$id_licencia_permiso_new,
        ];
        return $return;
    }
    public static function listRegisterLisensesPermits($id_entidad, $id_depto, $id_tipo_suspension, $id_sedearea,  $trabajador, $id_estado_lica_per, $per_page) {
               
        // $url_pdf=ComunData::ruta_url(url('humantalent/payments-tickets-worker-display'));
        //$url_dw= PaymentsData::ruta_url(url('humantalent/payments-tickets-worker-download'));
        $url_dw = ComunData::ruta_url(url('gth'));

        $query = DB::table('plla_licencia_permiso as a')
        ->join('moises.vw_trabajador as b', 'a.id_persona', '=', 'b.id_persona')
        ->join('plla_tipo_suspension as c', 'a.id_tipo_suspension', '=', 'c.id_tipo_suspension')
        ->join('org_sede_area as d', 'a.id_sedearea', '=', 'd.id_sedearea')
        ->join('org_area as e', 'd.id_area', '=', 'e.id_area')
        ->join('plla_estado_lica_per as f', 'a.id_estado_lica_per', '=', 'f.id_estado_lica_per')
        ->where('a.id_entidad', $id_entidad)
        ->whereraw(DB::raw("d.id_depto like '".$id_depto."%'"))
        ->whereraw(DB::raw("(c.id_tipo_suspension like '%".$id_tipo_suspension."%')"))
        ->whereraw(DB::raw("(a.id_sedearea like '%".$id_sedearea."%')"))
        ->whereraw("(upper(b.nombre) like upper('%".$trabajador."%')
                    or upper(b.nombre ||' ' || b.paterno ) like upper('%".$trabajador."%')
                    or upper(b.paterno ||' ' || b.materno ) like upper('%".$trabajador."%')
                    or b.num_documento like '%".$trabajador."%')")
        ->whereraw(DB::raw("(a.id_estado_lica_per like '%".$id_estado_lica_per."%')"))
        ->select('a.id_licencia_permiso', 'a.id_entidad', DB::raw("(b.nombre || ' ' || b.paterno || ' ' || b.materno) as nombres"), 
                'a.licencia_permiso_url', 'c.id_tipo_suspension', 'c.nombre_corto as nombre_corto_tipo_suspension', 'd.id_depto',
                'e.nombre as nombre_depto', 'a.motivo',
                DB::raw(" case when a.periodo='H' then to_char(a.fecha_desde, 'YYYY-MM-DD HH24:MI') else to_char(a.fecha_desde, 'YYYY-MM-DD') end as fecha_desde, 
                case when a.periodo='H' then to_char(a.fecha_hasta, 'HH24:MI') else to_char(a.fecha_hasta, 'YYYY-MM-DD') end as fecha_hasta"),
                DB::raw("to_char(a.fecha_desde, 'HH24:mm') as hora_inicio, to_char(a.fecha_hasta, 'HH24:mm') as hora_fin"),
                'a.periodo', 'a.dias', 'a.horas', 'f.nombre as estado_nombre', 'f.id_estado_lica_per', 'a.id_sedearea', 'a.id_persona','a.tiempo',
                'a.descripcion', 'a.enhoras', 'c.periodo as periodo_suspension',
                DB::raw("('".$url_dw."/'||a.licencia_permiso_url) as urls_dw"),
                DB::raw("substr(a.licencia_permiso_url,instr(a.licencia_permiso_url, '.', -1) +1,length(a.licencia_permiso_url)) as formato"),
                DB::raw("'1' as apto"))
                // DB::raw("regexp_substr(a.licencia_permiso_url,'[.^].*', 1) as formato")
                ->orderBy('a.id_licencia_permiso', 'desc')
        // ->orderBy('nombres', 'asc')
        ->paginate((int)$per_page);
        return $query;
    }
    public static function updateRegisterLisensesPermits($id_licencia_permiso,$id_user, $request) {
        $id_estado_lica_per=$request->id_estado_lica_per;
        $motivo_anula=$request->motivo_anula;
        $result = DB::table('plla_licencia_permiso')
                    ->where('id_licencia_permiso', $id_licencia_permiso)
                    ->update(['id_estado_lica_per' => $id_estado_lica_per,
                        'motivo_anula' => $motivo_anula,
                        'id_user_anula' => $id_user,
                        'fecha_anula' => DB::raw("sysdate")
                        ]);
        return $result;
                
    }
}
?>

