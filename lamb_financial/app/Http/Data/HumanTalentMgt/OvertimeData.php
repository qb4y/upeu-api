<?php
namespace App\Http\Data\HumanTalentMgt;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\HumanTalentMgt\ComunData;
use PDO;

class OvertimeData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function addOvertimeRegister($id_user, $request){
   
        $nerror = 0;
        $id_sobretiempo_new = '';
        $msgerror = '';
     
        for ($id = 1; $id <= 25; $id++) {
            $id_sobretiempo_new .= '0';
        }

        $id_sobretiempo             = $request->id_sobretiempo;
        $id_entidad                 = $request->id_entidad;
        $id_sedearea                = $request->id_sedearea;
        $id_persona                 = json_decode($request->id_persona);
        $tipo                       = $request->tipo;
        $fecha                      = $request->fecha;
        $hora_desde                 = $request->hora_desde;
        $hora_hasta                 = $request->hora_hasta;
        $motivo                     = $request->motivo;
        $id_estado_sobretiempo      = $request->id_estado_sobretiempo;
        $compensado                 = $request->compensado;
        $documento_url              = $request->documento_url;
        $fecha_compensar            = $request->fecha_compensar;
        $comentario_compensar       = $request->comentario_compensar;
        $horasrefrigerio             = $request->horasrefrigerio;
    //   dd($hora_desde, $fecha);
        
        $id_personas = array();

        $error = 0;
        $mensagges = '';
        $ites = 0;
        $doc = '';
        foreach ($id_persona as $datos) {

            $msgerror = '';
            for ($i = 1; $i <= 200; $i++) {
                $msgerror .= '0';
            }
            $id_sobretiempo_new = '';
            for ($id = 1; $id <= 25; $id++) {
                $id_sobretiempo_new .= '0';
            }
            $items = (object)$datos;
            // dd('hhh', $items, $id_entidad);
            //$items->id_persona;

            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_HUMAN_TALENT_MGT.SP_REGISTRAR_SOBRETIEMPO(
                                    :P_ITEM,
                                    :P_ID_SOBRETIEMPO,
                                    :P_ID_ENTIDAD,
                                    :P_ID_PERSONA,
                                    :P_TIPO,
                                    :P_ID_SEDEAREA,
                                    :P_FECHA,
                                    :P_MOTIVO,
                                    :P_HORA_DESDE,
                                    :P_HORA_HASTA,
                                    :P_ID_USER_REG,
                                    :P_CONPESADO,
                                    :P_DOCUMENTO_URL,
                                    :P_FECHA_COMPENSAR,
                                    :P_COMENTARIO_COMPENSAR,
                                    :P_HORASREFRIGERIO,
                                    :P_ERROR,
                                    :P_MSGERROR,
                                    :P_ID_SOBRETIEMPO_NEW); end;");
            $stmt->bindParam(':P_ITEM',                 $ites, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_SOBRETIEMPO',       $id_sobretiempo, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_ENTIDAD',           $id_entidad, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_PERSONA',           $items->id_persona, PDO::PARAM_INT);
            $stmt->bindParam(':P_TIPO',                 $tipo, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_SEDEAREA',          $id_sedearea, PDO::PARAM_INT);
            $stmt->bindParam(':P_FECHA',                $fecha, PDO::PARAM_STR);
            $stmt->bindParam(':P_MOTIVO',               $motivo, PDO::PARAM_STR);
            $stmt->bindParam(':P_HORA_DESDE',           $hora_desde, PDO::PARAM_STR);
            $stmt->bindParam(':P_HORA_HASTA',           $hora_hasta, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_USER_REG',          $id_user, PDO::PARAM_INT);
            $stmt->bindParam(':P_CONPESADO',            $compensado, PDO::PARAM_STR);
            $stmt->bindParam(':P_DOCUMENTO_URL',        $doc, PDO::PARAM_STR);
            $stmt->bindParam(':P_FECHA_COMPENSAR',      $fecha_compensar, PDO::PARAM_STR);
            $stmt->bindParam(':P_COMENTARIO_COMPENSAR', $comentario_compensar, PDO::PARAM_STR);
            $stmt->bindParam(':P_HORASREFRIGERIO',      $horasrefrigerio, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR',                $nerror, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR',             $msgerror, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_SOBRETIEMPO_NEW',   $id_sobretiempo_new, PDO::PARAM_STR);
            $stmt->execute();
            if($nerror==0){
                $file = $request->file('documento_url');
                if(!empty($file)){
                    $ext = $file->getClientOriginalExtension();
                    $destino ='gth';//por ahora
                    $uid=hash("md2",(string)microtime());
                    $archivo     = $uid.'.'.strtolower($ext);
                    $documento_url = $archivo;
                    $file->move($destino, $archivo); 
                    //actualizar ruta
                    if(!empty($documento_url)){
                        DB::table('eliseo.plla_sobretiempo')
                            ->where('id_sobretiempo', $id_sobretiempo_new)
                            ->update(['documento_url' => $documento_url]);
                    }
                }
            } else {
                $error++;
                $mensagges = $mensagges.$msgerror.' -';
            }
            $ites++;
        }

        if ($error == 0) {

            $return = [
                'nerror' => 0,
                'msgerror' => 'Ok',
            ];
        } else {
            $return = [
                'nerror' => 1,
                'msgerror' => $mensagges,
            ];  
        }
        return $return;
    }
    public static function listRegisterOvertime($id_entidad, $id_depto, $id_sedearea,  $trabajador, $id_estado_sobretiempo, $per_page) {
               
        $url_dw = ComunData::ruta_url(url('gth'));

        $query = DB::table('plla_sobretiempo as a')
        ->join('moises.vw_trabajador as b', 'a.id_persona', '=', 'b.id_persona')
        ->join('org_sede_area as d', 'a.id_sedearea', '=', 'd.id_sedearea')
        ->join('org_area as e', 'd.id_area', '=', 'e.id_area')
        ->join('plla_estado_sobretiempo as f', 'a.id_estado_sobretiempo', '=', 'f.id_estado_sobretiempo')
        ->where('a.id_entidad', $id_entidad)
        ->whereraw(DB::raw("d.id_depto like '".$id_depto."%'"))
        ->whereraw(DB::raw("(a.id_sedearea like '%".$id_sedearea."%')"))
        ->whereraw("(upper(b.nombre) like upper('%".$trabajador."%')
                    or upper(b.nombre ||' ' || b.paterno ) like upper('%".$trabajador."%')
                    or upper(b.paterno ||' ' || b.materno ) like upper('%".$trabajador."%')
                    or b.num_documento like '%".$trabajador."%')")
        ->whereraw(DB::raw("(a.id_estado_sobretiempo like '%".$id_estado_sobretiempo."%')"))
        ->select('a.id_sobretiempo', 'a.id_entidad',
                DB::raw("(b.nombre || ' ' || b.paterno || ' ' || b.materno) as nombres"), 
               'd.id_depto',
                'e.nombre as nombre_depto',
                'a.motivo',
                DB::raw("to_char(a.fecha, 'YYYY-MM-DD') as fecha"),
                'a.hora_hasta',
                'a.hora_desde',
                 DB::raw("(case when a.conpesado = 'S' then 'Si' when a.conpesado = 'N' then 'No' else '' end) as conpesado"),
                'a.comentario_compensar',
                DB::raw("to_char(a.fecha_compensar, 'YYYY-MM-DD') as fecha_compensar"),
                'f.nombre as estado_nombre',
                'f.id_estado_sobretiempo',
                'a.id_sedearea',
                'a.id_persona',
                'a.comentario',
                DB::raw("('".$url_dw."/'||a.documento_url) as urls_dw"),
                DB::raw("substr(a.documento_url,instr(a.documento_url, '.', -1) +1,length(a.documento_url)) as formato"),
                DB::raw("'1' as apto"))
                // DB::raw("regexp_substr(a.documento_url,'[.^].*', 1) as formato")
                ->orderBy('a.id_sobretiempo', 'desc')
        // ->orderBy('nombres', 'asc')
        ->paginate((int)$per_page);
        return $query;
    }
    public static function updateRegisterOvertime($id_sobretiempo, $request) {
        $id_estado_sobretiempo=$request->id_estado_sobretiempo;
        $result = DB::table('plla_sobretiempo')
                    ->where('id_sobretiempo', $id_sobretiempo)
                    ->update(['id_estado_sobretiempo' => $id_estado_sobretiempo]);
        return $result;
                
    }
    public static function updateRefusedOvertime($id_sobretiempo, $request) {
        $id_estado_sobretiempo=$request->id_estado_sobretiempo;
        $comentario=$request->comentario;
        $result = DB::table('plla_sobretiempo')
                    ->where('id_sobretiempo', $id_sobretiempo)
                    ->update(['id_estado_sobretiempo' => $id_estado_sobretiempo, 'comentario' => $comentario]);
        return $result;
                
    }
}
?>

