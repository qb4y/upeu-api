<?php
namespace App\Http\Data\HumanTalentMgt;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;

class AssistanceData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function addTypeShedule($request){ 
        // dd($request);
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $doblehorario = $request->doblehorario;
        $conrefrigerio = $request->conrefrigerio;
        $conrefrigerio2 = $request->conrefrigerio2;
        $horasrefrigerio = $request->horasrefrigerio;
        $horasrefrigerio2 = $request->horasrefrigerio2;
        $descripcion = $request->descripcion;
        $nombre = $request->nombre;
        $vigencia = 1;
        
        $id_tipo_horario =  ComunData::correlativo('plla_tipo_horario', 'id_tipo_horario');
        if($id_tipo_horario>0){
            $save = DB::table('plla_tipo_horario')->insert(
                [
                'id_tipo_horario' =>  $id_tipo_horario,
                'id_entidad' => $id_entidad,
                'id_depto' => $id_depto,
                'doblehorario' => $doblehorario,
                'conrefrigerio' => $conrefrigerio,
                'conrefrigerio2' => $conrefrigerio2,
                'horasrefrigerio' => $horasrefrigerio,
                'horasrefrigerio2' => $horasrefrigerio2,
                'descripcion' => $descripcion,
                'nombre' => $nombre,
                'vigencia' => $vigencia,
                ]
            );
         

            if($save){

                $response=[
                    'success'=> true,
                    'message'=>'',
                ];

            }else{
                $response=[
                    'success'=> false,
                    'message'=>'No se puede insertar',
                ];
            }
    
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se ha generado correlativo',
            ];
        }
        return $response;
        
    }
    public static  function listTypeShedule($id_entidad, $id_depto){
        $query= DB::table('plla_tipo_horario as a')
        ->where("a.id_entidad", '=', $id_entidad)
        ->whereraw(DB::raw("a.id_depto like '".$id_depto."%'"))
        ->select('a.id_tipo_horario',
        'a.nombre', 'a.horasrefrigerio', 'a.horasrefrigerio2', 'a.descripcion',
        DB::raw("(case when a.doblehorario = 'S' then 'Si' when a.doblehorario = 'N' then 'No' else '' end) as doblehorario"),
        DB::raw("(case when a.conrefrigerio = 'S' then 'Si' when a.conrefrigerio = 'N' then 'No' else '' end) as conrefrigerio"),
        DB::raw("(case when a.conrefrigerio2 = 'S' then 'Si' when a.conrefrigerio2 = 'N' then 'No' else '' end) as conrefrigerio2"))
        ->orderBy('a.id_tipo_horario', 'desc')
        ->get();
         return $query;
    }
    public static  function listTypeSheduleDetails($id_tipo_horario){
        $query= DB::table('plla_horario_detalle as a')
        ->join('plla_dias as b', 'a.id_dias', '=', 'b.id_dia')
        ->where("a.id_tipo_horario", '=', $id_tipo_horario)
        ->select('a.id_dias', 'a.id_tipo_horario', 'a.hora_entrada', 'a.hora_salida', 'a.hora_entrada_ref',
        'a.hora_salida_ref', 'a.num_horas', 'a.hora_entrada2', 'a.hora_salida2', 'a.hora_entrada_ref2', 'a.hora_salida_ref2', 'a.num_horas2', 'b.nombre as nombre_dia')
        ->orderBy('b.id_dia', 'asc')
        ->get();
         return $query;
    }
    public static function addTypeSheduleDetails($request){ 
        // dd($request);

        $id_tipo_horario = $request->id_tipo_horario;
        $details = $request->details;
        $fecha = date('Y-m-d');
        
        $obj = DB::table('plla_tipo_horario')->where('id_tipo_horario',$id_tipo_horario)
                ->select('horasrefrigerio','doblehorario','conrefrigerio','conrefrigerio2','horasrefrigerio2')
                ->first();

                foreach($details as $datos){
                   $items=(object)$datos;
                   
                   
                   $desde =  $fecha.' '.$items->hora_entrada.':00'; 
                   $hasta =  $fecha.' '.$items->hora_salida.':00';
                   
                   $objhora = DB::table(DB::raw('dual'))
                              ->select(DB::raw("round((to_date('".$hasta."') -  to_date('".$desde."'))*24,2) as nhoras"))
                               ->first();
                    $num_horas = $objhora->nhoras;
                    if($obj->conrefrigerio){
                        if($items->id_dia!=6){
                            $num_horas = $objhora->nhoras - $obj->horasrefrigerio;
                        }
                        
                    }
                   $num_horas2 = 0;
                   if($obj->doblehorario=='S'){
                       $desde2 =  $fecha.' '.$items->hora_entrada2.':00'; 
                       $hasta2 =  $fecha.' '.$items->hora_salida2.':00';
                       
                       $objhora1 = DB::table(DB::raw('dual'))
                              ->select(DB::raw("round((to_date('".$hasta2."') -  to_date('".$desde2."'))*24,2) as nhoras"))
                               ->first();
                        $num_horas2 = $objhora1->nhoras;
                        if($obj->conrefrigerio2){
                            $num_horas2 = $objhora1->nhoras - $obj->horasrefrigerio2;
                        }
                   }
                   
                   $saveDetails =  DB::table('plla_horario_detalle')->insert(
                            ['id_tipo_horario' => $id_tipo_horario,
                             'id_dias' => $items->id_dia,
                             'hora_entrada' => $items->hora_entrada,
                             'hora_salida' => $items->hora_salida,
                             'hora_entrada_ref' => $items->hora_entrada_ref,
                             'hora_salida_ref' => $items->hora_salida_ref,
                             'num_horas' => $num_horas,
                             'hora_entrada2' => $items->hora_entrada2,
                             'hora_salida2' => $items->hora_salida2,
                             'hora_entrada_ref2' => $items->hora_entrada_ref2,
                             'hora_salida_ref2' => $items->hora_salida_ref2,
                             'num_horas2' => $num_horas2,
                            ]
                        );
                
                }
                
            if($saveDetails){

                $response=[
                    'success'=> true,
                    'message'=>'',
                ];

            }else{
                $response=[
                    'success'=> false,
                    'message'=>'No se puede insertar detalle',
                ];
            }
    
        return $response;
    }
    public static function deleteTypeSheduleDetails($id_dias)
    {
        $query = DB::table('plla_horario_detalle')->where('id_dias', $id_dias)->delete();
        return $query;
    }
    public static function deleteTypeShedule($id_tipo_horario) {
        $response=[
            'success'=> true,
            'message'=>'Se elimino Satisfactoriamente',
        ];
        $contar= DB::table('moises.persona_natural_trabajador as a')
        ->where("a.id_tipo_horario", '=', $id_tipo_horario)
        ->count();
        if ($contar==0) {
            
                    $querys = DB::table('plla_horario_detalle')->where('id_tipo_horario', $id_tipo_horario)->delete();
                    $query2 = DB::table('plla_tipo_horario')->where('id_tipo_horario', $id_tipo_horario)->delete();
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se puede eliminar hay'.' '.$contar.' '.'persona(s) asignada(s) a ese horario',
            ];
        }
        // dd($response);
        return $response;
    }
    public static  function showTypeShedule($id_tipo_horario){
        $query= DB::table('plla_tipo_horario as a')
        ->where("a.id_tipo_horario", '=', $id_tipo_horario)
        ->select('a.id_tipo_horario',
        'a.id_entidad',
        'a.id_depto',
        'a.nombre',
        'a.doblehorario',
        'a.conrefrigerio',
        'a.horasrefrigerio',
        'a.conrefrigerio2',
        'a.horasrefrigerio2',
        'a.descripcion')
        ->get()->shift();
         return $query;
    }
    public static function updateTypeShedule($id_tipo_horario, $request) {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $doblehorario = $request->doblehorario;
        $conrefrigerio = $request->conrefrigerio;
        $conrefrigerio2 = $request->conrefrigerio2;
        $horasrefrigerio = $request->horasrefrigerio;
        $horasrefrigerio2 = $request->horasrefrigerio2;
        $descripcion = $request->descripcion;
        $nombre = $request->nombre;
        $vigencia = 1;
        $result = DB::table('plla_tipo_horario')
                    ->where('id_tipo_horario', $id_tipo_horario)
                    ->update([
                    'id_entidad' => $id_entidad,
                    'id_depto' => $id_depto,
                    'doblehorario' => $doblehorario,
                    'conrefrigerio' => $conrefrigerio,
                    'conrefrigerio2' => $conrefrigerio2,
                    'horasrefrigerio' => $horasrefrigerio,
                    'horasrefrigerio2' => $horasrefrigerio2,
                    'descripcion' => $descripcion,
                    'nombre' => $nombre,
                    'vigencia' => $vigencia]);
                }
     public static  function listTypeSheduleDetailsShow($request){
         $id_tipo_horario = $request->id_tipo_horario;
         $id_dias = $request->id_dias;
         $query= DB::table('plla_horario_detalle as a')
         ->join('plla_dias as b', 'a.id_dias', '=', 'b.id_dia')
         ->where("a.id_tipo_horario", '=', $id_tipo_horario)
         ->where("a.id_dias", '=', $id_dias)
         ->select('a.id_dias', 'a.id_tipo_horario', 'a.hora_entrada', 'a.hora_salida', 'a.hora_entrada_ref',
         'a.hora_salida_ref', 'a.num_horas', 'a.hora_entrada2', 'a.hora_salida2', 'a.hora_entrada_ref2', 'a.hora_salida_ref2', 'a.num_horas2', 'b.nombre as nombre_dia')
         ->get()->shift();
          return $query;
     }
     public static function updateTypeSheduleDetails($id_dias, $request) {
        //  dd($id_dias, $request);
        $id_tipo_horario = $request->id_tipo_horario;
        $hora_entrada = $request->hora_entrada;
        $hora_salida = $request->hora_salida;
        $hora_entrada_ref = $request->hora_entrada_ref;
        $hora_salida_ref = $request->hora_salida_ref;
        $num_horas = $request->num_horas;
        $hora_entrada2 = $request->hora_entrada2;
        $hora_salida2 = $request->hora_salida2;
        $hora_entrada_ref2 = $request->hora_entrada_ref2;
        $hora_salida_ref2 = $request->hora_salida_ref2;
        $num_horas2 = $request->num_horas2;
        
        
        $fecha = date('Y-m-d');
        
        $obj = DB::table('plla_tipo_horario')->where('id_tipo_horario',$id_tipo_horario)
                ->select('horasrefrigerio','doblehorario','conrefrigerio','conrefrigerio2','horasrefrigerio2')
                ->first();
        
        
        $desde =  $fecha.' '.$request->hora_entrada.':00'; 
        $hasta =  $fecha.' '.$request->hora_salida.':00';

        $objhora = DB::table(DB::raw('dual'))
                   ->select(DB::raw("round((to_date('".$hasta."') -  to_date('".$desde."'))*24,2) as nhoras"))
                    ->first();
         $num_horas = $objhora->nhoras;
         if($obj->conrefrigerio){
             if($id_dias!=6){
                $num_horas = $objhora->nhoras - $obj->horasrefrigerio; 
             }
             
         }
        $num_horas2 = 0;
        if($obj->doblehorario=='S'){
            $desde2 =  $fecha.' '.$request->hora_entrada2.':00'; 
            $hasta2 =  $fecha.' '.$request->hora_salida2.':00';

            $objhora1 = DB::table(DB::raw('dual'))
                   ->select(DB::raw("round((to_date('".$hasta2."') -  to_date('".$desde2."'))*24,2) as nhoras"))
                    ->first();
             $num_horas2 = $objhora1->nhoras;
             if($obj->conrefrigerio2){
                 if($id_dias!=6){
                    $num_horas2 = $objhora1->nhoras - $obj->horasrefrigerio2;
                 }
             }
        }

        $result = DB::table('plla_horario_detalle')
                    ->where('id_dias', $id_dias)
                    ->where('id_tipo_horario', $id_tipo_horario)
                    ->update(
                        [
                        'hora_entrada' => $hora_entrada,
                        'hora_salida' => $hora_salida,
                        'hora_entrada_ref' => $hora_entrada_ref,
                        'hora_salida_ref' => $hora_salida_ref,
                        'num_horas' => $num_horas,
                        'hora_entrada2' => $hora_entrada2,
                        'hora_salida2' => $hora_salida2,
                        'hora_entrada_ref2' => $hora_entrada_ref2,
                        'hora_salida_ref2' => $hora_salida_ref2,
                        'num_horas2' => $num_horas2,
                       ]
                );
        }
        public static  function listControlAssist($id_entidad, $id_persona, $id_anho, $id_mes){
            // dd($id_entidad, $id_persona);
            $query= DB::table('plla_asistencia as a')
            ->join('plla_tipo_horario as b', 'a.id_tipo_horario', '=', 'b.id_tipo_horario')
            // ->leftjoin('plla_horario_detalle as d', 'b.id_tipo_horario', '=', 'd.id_tipo_horario')
            ->join('moises.tipo_control_personal as c', 'a.id_tipo_control_personal', '=', 'c.id_tipo_control_personal')
            ->leftjoin('plla_motivo_asist as ma', 'ma.id_motivo_asist', '=', 'a.id_motivo_asist')
            ->where("a.id_entidad", '=', $id_entidad)
            ->where("a.id_persona", '=', $id_persona)
            ->whereraw("to_number(to_char(a.fecha,'YYYY')) = ".$id_anho."")
            ->whereraw("to_number(to_char(a.fecha,'MM')) = ".$id_mes."")
            ->select('a.id_asistencia', 
            'a.id_entidad',
            'a.id_sedearea', 
            'a.id_tipo_horario',
            'b.nombre as nombre_tipo_horario',
            'a.id_tipo_control_personal', 
            'c.nombre as nombre_control_personal',
            DB::raw("to_char(a.fecha, 'YYYY-MM-DD') as fecha"),
            DB::raw("to_char(a.fecha, 'DD') as fechacorta"),        
            // DB::raw("to_char(a.fecha, 'D') as dia_semana"),
            // DB::raw("to_char(a.fecha, 'DAY', 'NLS_DATE_LANGUAGE=SPANISH') as nombre_dia"),
            // 'a.fecha',
            DB::raw("(select count(*) from plla_feriados m where to_char(a.fecha,'YYYYMMDD')=to_char(m.fecha,'YYYYMMDD')) as feriado"),
            DB::raw("(select di.nombre from plla_dias di where di.id_dia=to_number(TO_CHAR(a.fecha, 'D', 'NLS_DATE_LANGUAGE=ENGLISH'))) as nombre_dia"),
            DB::raw("to_number(TO_CHAR(a.fecha, 'D', 'NLS_DATE_LANGUAGE=ENGLISH')) as dia"),
            // DB::raw("(case when to_char(a.fecha, 'D') = 7 then 'Reposo' else 'labor' end) as esLaborable"),
            'a.hora_base_ent',
            'a.hora_base_sal',
            'a.hora_base_ent_ref',
            'a.hora_base_sal_ref',
            'a.num_horas_base',
            'a.hora_entrada',
            'a.hora_salida',
            'a.hora_entrada_ref',
            'a.hora_salida_ref',
            'a.num_horas',
            'ma.id_motivo_asist',
            'ma.nombrecorto as motivo_asist',
            'a.esmodificado',
            'a.hora_entrada_mod',
            'a.hora_salida_mod',
            'a.hora_entrada_ref_mod',
            'a.hora_salida_ref_mod',
            'a.num_horas_final',
            'a.num_tole_tar',
            'a.num_minutos_tar',
            'a.num_tole_tar_ref',
            'a.num_minutos_tar_ref',
            'a.hora_entrada_pla',
            'a.hora_salida_pla',
            'a.num_ref_pla',
            'a.num_horas_pla',
            DB::raw("coalesce(a.num_minutos_tar,0) + coalesce(a.num_minutos_tar_ref,0) as total_tar")
            )
            ->orderBy('a.fecha','asc')
            ->get();
             return $query;
        }
        public static  function sumControlAssist($id_entidad, $id_persona, $id_anho, $id_mes){
            // dd($id_entidad, $id_persona);
            $query= DB::table('plla_asistencia as a')
            ->where("id_entidad", '=', $id_entidad)
            ->where("id_persona", '=', $id_persona)
            ->whereraw("to_number(to_char(fecha,'YYYY')) = ".$id_anho."")
            ->whereraw("to_number(to_char(fecha,'MM')) = ".$id_mes."")
            ->select(
                    DB::raw("
                            coalesce(sum(num_horas_base),0) as num_horas_base, 
                            coalesce(sum(num_horas),0) as num_horas,
                            coalesce(sum(num_horas_final),0) as num_horas_final,
                            coalesce(sum(num_minutos_tar),0) as num_minutos_tar,
                            coalesce(sum(num_minutos_tar_ref),0) as num_minutos_tar_ref,
                            coalesce(sum(coalesce(num_minutos_tar,0) + coalesce(num_minutos_tar_ref,0)),0) as total_tar,
                            coalesce(sum(num_horas_pla),0) as num_horas_pla
                            ")
            )
            ->first();
             return $query;
        }

        public static  function listControlAssistShow($id_asistencia){
            // dd($id_asistencia);
            $query= DB::table('plla_asistencia as a')
            ->where("a.id_asistencia", '=', $id_asistencia)
            ->select('a.id_asistencia', 
            'a.esmodificado',
            'a.motivo_mod',
            'a.hora_entrada_mod',
            'a.hora_salida_mod',
            'a.hora_entrada_ref_mod',
            'a.hora_salida_ref_mod')
            ->get()->shift();
            //  dd($query);
             return $query;
        }
        public static function updateControlAssist($id_asistencia, $request, $id_user) {
            //  dd($id_dias, $request);
            $esmodificado = $request->esmodificado;
            $hora_entrada_mod = $request->hora_entrada_mod;
            $hora_salida_mod = $request->hora_salida_mod;
            $hora_entrada_ref_mod = $request->hora_entrada_ref_mod;
            $hora_salida_ref_mod = $request->hora_salida_ref_mod;
            $motivo_mod = $request->motivo_mod;
            
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_HUMAN_TALENT_MGT.SP_MODIFICAR_ASISTENCIA(
                                    :P_ID_ASISTENCIA, :P_HORA_ENTRADA_MOD, :P_HORA_SALIDA_MOD, :P_HORA_ENTRADA_REF_MOD, :P_HORA_SALIDA_REF_MOD, :P_ID_USER_REG,:P_MOTIVO_MOD
                                         ); end;");
            $stmt->bindParam(':P_ID_ASISTENCIA', $id_asistencia, PDO::PARAM_STR);
            $stmt->bindParam(':P_HORA_ENTRADA_MOD', $hora_entrada_mod, PDO::PARAM_STR);
            $stmt->bindParam(':P_HORA_SALIDA_MOD', $hora_salida_mod, PDO::PARAM_STR);
            $stmt->bindParam(':P_HORA_ENTRADA_REF_MOD', $hora_entrada_ref_mod, PDO::PARAM_STR);
            $stmt->bindParam(':P_HORA_SALIDA_REF_MOD', $hora_salida_ref_mod, PDO::PARAM_STR);
            $stmt->bindParam(':P_ID_USER_REG', $id_user, PDO::PARAM_INT);
            $stmt->bindParam(':P_MOTIVO_MOD', $motivo_mod, PDO::PARAM_STR);
            $stmt->execute();
        
           
   }
   public static  function listManualMarcation($id_asistencia){
            // dd($id_asistencia);
        $asis= DB::table('plla_asistencia as a')
            ->where("a.id_asistencia", '=', $id_asistencia)
            ->select('a.id_persona', 
            DB::raw("to_char(a.fecha,'YYYY-MM-DD') as fecha")
                    )
            ->first();
        //dd($asis);
        $data = DB::table('plla_asist_marcacion')
        ->select(
            'id_persona',
            'idasistencia',
            'idpersonal',
            'fecha',
            DB::raw("to_char(fechahora,'HH24:MI:SS') as fechahora"),
            'tipoingreso',
            'tipomarcacion',
            'num_marcador',
            'numdocumento',
            'fotocheck'
            )
        ->where('id_persona',$asis->id_persona)
        ->where('fecha',$asis->fecha)
        ->orderBy('fechahora','asc')
        ->get(); 
            //  dd($query);
        return $data;
    }
    public static function copyManualMarcation($desde,$hasta,$id_persona){
        $doc ='0';
        if($id_persona>0){
            $obj = DB::table('moises.persona_natural')
                    ->where('id_persona',$id_persona)
                    ->select('num_documento')
                    ->first();
            if(!empty($obj)){
                if(strlen($obj->num_documento)>0){
                    $doc =$obj->num_documento;
                }
            }
        }
        
       
        $data = DB::connection('siscop')
        ->table('asistencia a')
        ->join('personal as p','p.idpersonal','=','a.idpersonal')
        ->select(
            //DB::raw('row_number() over (partition by a.fecha order by a.idasistencia) as idasistencia'),
            'a.idpersonal',
            'a.fecha',
            'a.fechahora',
            'a.tipoingreso',
            'a.tipomarcacion',
            'a.num_marcador',
            'p.ndocumento as numdocumento',
            'a.fotocheck'
            )
        ->whereBetween('a.fecha',[$desde,$hasta])
        ->whereraw("case when '".$doc."' ='0' then '0x0' else p.ndocumento end = case when '".$doc."' ='0' then '0x0' else '".$doc."' end")
        ->orderBy('a.idpersonal','asc')
        ->orderBy('a.fecha','asc')
        ->orderBy('a.fechahora','asc')
        ->get(); 
        
         
          //dd($data);
        DB::table('plla_asist_marcacion_tmp')->delete();
         
                 
        if(count($data)>0){
            $j=1;
            foreach($data as $row){
                DB::table('plla_asist_marcacion_tmp')->insert([
                    'idasistencia'=>$j,//$row->idasistencia,
                    'idpersonal'=>$row->idpersonal,
                    'fecha'=>$row->fecha,
                    'fechahora'=>$row->fechahora,
                    'tipoingreso'=>$row->tipoingreso,
                    'tipomarcacion'=>$row->tipomarcacion,
                    'num_marcador'=>$row->num_marcador,
                    'numdocumento'=>$row->numdocumento,
                    'fotocheck'=>$row->fotocheck
                    
                ]
                    
                );
                
                $j++;
            }
        }
        
               
        DB::table('plla_asist_marcacion_tmp')->update(['id_persona' => DB::raw("fc_mgt_obtener_idpersona(numdocumento)")]);
        
        DB::table('plla_asist_marcacion')
                ->whereBetween('fecha',[$desde,$hasta])
                ->whereraw("case when '".$doc."' ='0' then 0 else id_persona end = case when '".$doc."' ='0' then 0 else ".$id_persona." end")
                ->delete();
        
        $data = DB::table('plla_asist_marcacion_tmp')
        ->select(
            //DB::raw('row_number() over (partition by id_persona,fecha order by idasistencia) as items'),
            'id_persona',
            'idasistencia',
            'idpersonal',
            'fecha',
            'fechahora',
            'tipoingreso',
            'tipomarcacion',
            'num_marcador',
            'numdocumento',
            'fotocheck'
            )
        ->whereraw("coalesce(id_persona,0)>0")
        ->orderBy('id_persona','asc')
        ->orderBy('fechahora','asc')
        ->get(); 
        
      
        
        if(count($data)>0){
            $j=1;
            $dato='';
            foreach($data as $row){
                if($dato!=$row->id_persona.$row->fecha){
                    $j=1;
                }
                DB::table('plla_asist_marcacion')->insert([
                    'items'=>$j,//$row->items,
                    'id_persona'=>$row->id_persona,
                    'idasistencia'=>$row->idasistencia,
                    'idpersonal'=>$row->idpersonal,
                    'fecha'=>$row->fecha,
                    'fechahora'=>$row->fechahora,
                    'tipoingreso'=>$row->tipoingreso,
                    'tipomarcacion'=>$row->tipomarcacion,
                    'num_marcador'=>$row->num_marcador,
                    'numdocumento'=>$row->numdocumento,
                    'fotocheck'=>$row->fotocheck,
                    'fecha_proc'=>DB::raw('sysdate')
                ]
                    
                );
                $j++;
                $dato=$row->id_persona.$row->fecha;
            }
        }
   
    }
    public static function listTrabajadorAsistenceControl($id_entidad, $id_depto, $id_sedearea, $persona, $per_page) {
        $q = DB::table('moises.vw_trabajador a');
        $q->join('org_sede_area b', 'a.id_sedearea', '=', 'b.id_sedearea'  );
        $q->join('moises.tipo_documento c', 'a.id_tipodocumento', '=', 'c.id_tipodocumento');
        $q->join('moises.situacion_trabajador d', 'a.id_situacion_trabajador', '=', 'd.id_situacion_trabajador');
        $q->join('moises.condicion_laboral e', 'a.id_condicion_laboral', '=', 'e.id_condicion_laboral');
        $q->join('moises.tipo_tiempo_trabajo f', 'a.id_tipo_tiempo_trabajo', '=', 'f.id_tipo_tiempo_trabajo');
        $q->join('org_area g', 'b.id_area', '=', 'g.id_area');
        $q->leftjoin('plla_tipo_horario th', 'th.id_tipo_horario', '=', 'a.id_tipo_horario');
        $q->leftjoin('moises.tipo_control_personal tc', 'tc.id_tipo_control_personal', '=', 'a.id_tipo_control_personal');
        $q->where('b.id_entidad', $id_entidad);
        $q->whereraw("b.id_depto like '".$id_depto."%'");
       if (strlen($id_sedearea)>0) {
           $q->where('a.id_sedearea', $id_sedearea);
           }
       if (strlen($persona)>0) {
           $q->whereraw("(upper(a.nombre) like upper('%".$persona."%')
        or upper(a.nombre ||' ' || a.paterno ) like upper('%".$persona."%')
        or upper(a.paterno ||' ' || a.materno ) like upper('%".$persona."%')
        or a.num_documento like '%".$persona."%')");
        }
        $q->select(
            'a.id_persona',
            'b.id_entidad',
            'b.id_depto',
            DB::raw("(a.nombre || ' ' || a.paterno || ' ' || a.materno) as nombres"),
            'g.nombre as nombre_area',
            'a.num_documento',
            'c.siglas',
            'a.fecha_inicio',
            'a.fecha_fin_previsto',
            'a.fecha_fin_efectivo',
            'th.nombre as tipo_horario',
            'tc.nombre as tipo_control_personal',
            'd.nombre as nombre_corto',
            'd.id_situacion_trabajador',
            'e.nombre as nombre_condicion',
            'f.nombre as tiempo_trabajo');
            $q->orderBy('a.nombre','asc');
           $data = $q->paginate((int)$per_page);
           //  ->get();
            return $data;
       }
}
?>

