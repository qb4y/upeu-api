<?php
namespace App\Http\Data\HumanTalentMgt;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Scalegroup;
use App\Models\Position;
use App\Models\Parameter;
use App\Models\Parametervalue;
use App\Models\ProfilePosition;
use App\Http\Data\HumanTalentMgt\ComunData;
use PDO;

class ConfigurationData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    
    public static function ListScaleGroups($id_nivel,$nombre,$perpage){
        $q = DB::table('plla_grupo_escala as a');
                $q->join('plla_nivel as b','b.id_nivel','=','a.id_nivel');
                $q->join('plla_grado as c','c.id_grado','=','a.id_grado');
                $q->join('plla_subgrado as d','d.id_subgrado','=','a.id_subgrado');
                if(strlen($id_nivel)>0){
                    $q->where('a.id_nivel',$id_nivel);
                }
                $q->whereraw(ComunData::fnBuscar('a.nombre').' like '.ComunData::fnBuscar("'%".$nombre."%'"));
                $q->select('a.*','b.nombre as nivel', 'c.nombre as grado', 'd.nombre as subgrado');
                $q->orderBy('a.id_nivel','asc');
                $q->orderBy('a.id_grado','asc');
                $q->orderBy('a.id_subgrado','asc');
                $q->orderBy('a.nombre','asc');
        $scalegroups=$q->paginate($perpage);
        
        return $scalegroups;
    }
    public static  function ShowScaleGroup($id_grupo_escala){
        $scalegroup = Scalegroup::find($id_grupo_escala);
        return $scalegroup;
    }
    public static function AddScaleGroup($request){
        $id_grupo_escala = ComunData::correlativo('plla_grupo_escala', 'id_grupo_escala');
        if($id_grupo_escala>0){
            
            //$count = Scalegroup::whereraw("FC_BUSCAR_TEXTO(nombre) = FC_BUSCAR_TEXTO('".trim($request->nombre)."')")->count();
            $count = Scalegroup::whereraw(ComunData::fnBuscar('nombre').' like '.ComunData::fnBuscar("'%".trim($request->nombre)."%'"))->count();
            if($count==0){
                $scalegroup= new Scalegroup();
                $scalegroup->id_grupo_escala=$id_grupo_escala;
                $scalegroup->nombre=$request->nombre;
                $scalegroup->descripcion=$request->descripcion;
                $scalegroup->id_nivel=$request->id_nivel;
                $scalegroup->id_grado=$request->id_grado;
                $scalegroup->id_subgrado=$request->id_subgrado;
                $scalegroup->vigencia=1;
                $answer = $scalegroup->save();
                if($answer){
                    $response=[
                        'success'=> true,
                        'message'=>'',
                        'scalegroup'=>$scalegroup
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
                        'message'=>$request->nombre.' ya existe',
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
    public static function UpdateScaleGroup($id_grupo_escala,$request){

        $scalegroup= Scalegroup::find($id_grupo_escala);
        if(!empty($scalegroup)){
            $count = Scalegroup::whereraw("FC_BUSCAR_TEXTO(nombre) = FC_BUSCAR_TEXTO('".trim($request->nombre)."')")->where('id_grupo_escala','<>',$id_grupo_escala)->count();
            
            if($count==0){
                $scalegroup->nombre=$request->nombre;
                $scalegroup->descripcion=$request->descripcion;
                $scalegroup->id_nivel=$request->id_nivel;
                $scalegroup->id_grado=$request->id_grado;
                $scalegroup->id_subgrado=$request->id_subgrado;
                $scalegroup->vigencia=$request->vigencia;
                $answer = $scalegroup->save();
                if($answer){
                    $response=[
                        'success'=> true,
                        'message'=>'',
                        'scalegroup'=>$scalegroup
                    ];
                }else{
                    $response=[
                        'success'=> false,
                        'message'=>'No se puede modificar',
                    ];
                }
            }else{
                $response=[
                    'success'=> false,
                    'message'=>$request->nombre.' ya existe',
                ];
            }
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se ha encontrado grupo escala',
            ];
        }
        return $response;
    }
    public static function DeleteScaleGroup($id_grupo_escala){
        $scalegroup= Scalegroup::find($id_grupo_escala);
        if(!empty($scalegroup)){
            
            $rows = Scalegroup::destroy($id_grupo_escala);
            if($rows>0){
                $response=[
                    'success'=> true,
                    'message'=>''
                ];
            }else{
                $response=[
                    'success'=> false,
                    'message'=>'No se puede eliminar',
                ];
            }

        }else{
            $response=[
                'success'=> false,
                'message'=>'No se ha encontrado grupo escala para eliminar',
            ];
        }
        return $response;
    }
    
    //puesto
    public static function ListPositions($nombre, $per_page){

        $querys= DB::table('plla_puesto as a')
        ->join('plla_grupo_escala as b', 'a.id_grupo_escala', '=', 'b.id_grupo_escala')
        ->leftjoin('plla_grupo_compentencia as c', 'a.id_grupo_compentencia', '=', 'c.id_grupo_compentencia')
        ->whereraw(ComunData::fnBuscar('a.nombre').' like '.ComunData::fnBuscar("'%".$nombre."%'"))
        // ->whereraw("FC_BUSCAR_TEXTO(a.nombre) like FC_BUSCAR_TEXTO('%".$nombre."%') ")
        ->select('a.id_puesto',
        'a.id_grupo_escala',
        'a.nombre',
        'a.descripcion',
        'a.vigencia',
        'b.nombre as nombre_grupo', 'c.nombre as nombre_competencia')
        ->orderBy('a.nombre','asc')
        ->paginate((int)$per_page);
        return $querys;
    }
    public static  function ShowPosition($id_puesto){
        // dd('xcfdf');
        $query= DB::table('plla_puesto as a')
        ->join('plla_grupo_escala as b', 'a.id_grupo_escala', '=', 'b.id_grupo_escala')
        ->where("a.id_puesto", '=', $id_puesto)
        ->select('a.id_puesto',
        'a.id_grupo_escala',
        'a.nombre',
        'a.descripcion',
        'a.vigencia',
        'a.id_grupo_compentencia_org',
        'a.id_grupo_compentencia',
        'b.nombre as nombre_grupo')
        ->get()->shift();
        // $position = Position::find($id_puesto);
        return $query;
    }
    public static function AddPosition($request){
        $id_puesto = ComunData::correlativo('plla_puesto', 'id_puesto');
        if($id_puesto>0){
            $count = Position::whereraw(ComunData::fnBuscar('nombre').' like '.ComunData::fnBuscar("'%".trim($request->nombre)."%'"))->count();
            // $count = Position::whereraw("FC_BUSCAR_TEXTO(nombre) = FC_BUSCAR_TEXTO('".trim($request->nombre)."')")->count();
            if($count==0){
            $position= new Position();
            $position->id_grupo_escala=$request->id_grupo_escala;
            $position->id_puesto=$id_puesto;
            $position->nombre=$request->nombre;
            $position->descripcion=$request->descripcion;
            $position->id_grupo_compentencia_org=$request->id_grupo_compentencia_org;
            $position->id_grupo_compentencia=$request->id_grupo_compentencia;
            $position->vigencia=1;
            $answer = $position->save();
            if($answer){
                $response=[
                    'success'=> true,
                    'message'=>'',
                    'position'=>$position
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
                    'message'=>$request->nombre.' ya existe',
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
    public static function UpdatePosition($id_puesto,$request){
        $position= Position::find($id_puesto);
        if(!empty($position)){
            $count = Position::whereraw(ComunData::fnBuscar('nombre').' = '.ComunData::fnBuscar("'".trim($request->nombre)."'"))->where('id_puesto','<>',$id_puesto)->count();
            // $count = Position::whereraw("FC_BUSCAR_TEXTO(nombre) = FC_BUSCAR_TEXTO('".trim($request->nombre)."')")->where('id_puesto','<>',$id_puesto)->count();
            if($count==0){
            $position->id_grupo_escala=$request->id_grupo_escala;
            $position->nombre=$request->nombre;
            $position->descripcion=$request->descripcion;
            $position->id_grupo_compentencia_org=$request->id_grupo_compentencia_org;
            $position->id_grupo_compentencia=$request->id_grupo_compentencia;
            $position->vigencia=$request->vigencia;
            $answer = $position->save();
            if($answer){
                $response=[
                    'success'=> true,
                    'message'=>'',
                    'position'=>$position
                ];
            }else{
                $response=[
                    'success'=> false,
                    'message'=>'No se puede modificar',
                ];
            }
        }else{
            $response=[
                'success'=> false,
                'message'=>$request->nombre.' ya existe',
            ];
        }
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se ha encontrado grupo escala',
            ];
        }
        return $response;
    }
    public static function DeletePosition($id_puesto){
        $position= Position::find($id_puesto);
        if(!empty($position)){
            $rows = Position::destroy($id_puesto);
            if($rows>0){
                $response=[
                    'success'=> true,
                    'message'=>''
                ];
            }else{
                $response=[
                    'success'=> false,
                    'message'=>'No se puede eliminar',
                ];
            }
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se ha encontrado grupo escala para eliminar',
            ];
        }
        return $response;
    }
    public static function ListParameters($id_entidad, $id_anho, $per_page) {
            $query = DB::table('plla_parametros as a')
            ->join('plla_parametros_valor as b', 'a.id_parametro', '=', 'b.id_parametro')
            ->where( 'b.id_entidad',$id_entidad)
            ->where("b.id_anho",$id_anho)
            ->select('a.id_parametro',
            'b.id_parametro_valor',
            'a.codigo',
            'a.nombre',
            'a.comentario',
            'a.formula',
            'b.eje_formula',
            'b.importe',
            'b.id_entidad',
            'b.id_anho')
            ->orderBy('a.orden', 'asc')
            ->paginate((int)$per_page); 
                return $query;
        }
        public static function procParametro($id_anho, $id_entidad){
            // dd('hhh', $id_anho, $id_entidad);
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_HUMAN_TALENT_MGT.SP_GENERAR_PARAMETROS(
                                    :P_ID_ENTIDAD, :P_ID_ANHO
                                         ); end;");
            $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
            $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
            $stmt->execute(); 
    
        }
        public static function updateParametro($data, $id_parametro_valor)
        {
            $result = DB::table('PLLA_PARAMETROS_VALOR')
                ->where('id_parametro_valor', $id_parametro_valor)
                ->update($data);
                $object = DB::table('plla_parametros_valor')
                ->where('id_parametro_valor', $id_parametro_valor)->first();
                ConfigurationData::procParametro($object->id_anho, $object->id_entidad);
                return $result;
        }
        public static function AddProfilePosition($request){
            $id_perfil_puesto = ComunData::correlativo('plla_perfil_puesto', 'id_perfil_puesto');
            if($id_perfil_puesto>0){
                $profileposition= new ProfilePosition();
                $profileposition->id_perfil_puesto=$id_perfil_puesto;
                $profileposition->id_puesto=$request->id_puesto;
                $profileposition->id_sedearea=$request->id_sedearea;
                $profileposition->id_perfil_puesto_jefe=$request->id_perfil_puesto_jefe;
                $profileposition->nivel=$request->nivel;
                $profileposition->email=$request->email;
                $profileposition->id_tipo_control_personal=$request->id_tipo_control_personal;
                $profileposition->bono=$request->bono;
                $profileposition->id_tipo_horario=$request->id_tipo_horario;
                $answer = $profileposition->save();
                if($answer){
                    $response=[
                        'success'=> true,
                        'message'=>'',
                        'profileposition'=>$profileposition
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
        public static function ListProfilePositions($id_sedearea){
            
            
            $q = DB::table('plla_perfil_puesto as a');
            $q->join('org_sede_area as b', 'a.id_sedearea', '=', 'b.id_sedearea');
            $q->join('plla_puesto as d', 'a.id_puesto', '=', 'd.id_puesto');
            $q->where("a.id_sedearea",$id_sedearea);
            $q->where("a.nivel",0);
            $q->select('a.id_perfil_puesto',
            'a.id_puesto',
            'a.id_sedearea',
            'b.id_depto',
            'd.nombre',
            DB::raw("(select count(*) from moises.persona_natural_trabajador x where x.id_sedearea=a.id_sedearea and x.id_puesto=a.id_puesto) as cantidad_trab_actual"));
            $q->orderBy('a.id_perfil_puesto', 'asc') ;
            $data = $q->get(); 
            
            $datos=array();
            
            foreach($data as $row){
                $items = array();
                $items['id_perfil_puesto']= $row->id_perfil_puesto;
                $items['id_sedearea']= $row->id_sedearea;
                $items['width']='200px';
                $items['nivel']= '1';
                $items['nombre']= $row->nombre;
                $items['id_puesto']= $row->id_puesto;
                $items['cantidad_trab_actual']= $row->cantidad_trab_actual;
                $items['children']=ConfigurationData::ChildrenPosition(1, $id_sedearea,$row->id_perfil_puesto);
                $datos[]=$items;
            }
            
             
            return $datos;

        }
        private static function ChildrenPosition($nivel, $id_sedearea,$id_perfil_puesto){
            $q = DB::table('plla_perfil_puesto as a');
            $q->join('org_sede_area as b', 'a.id_sedearea', '=', 'b.id_sedearea');
            $q->join('plla_puesto as d', 'a.id_puesto', '=', 'd.id_puesto');
            $q->where("a.id_sedearea",$id_sedearea);
            $q->where("a.id_perfil_puesto_jefe",$id_perfil_puesto);
            $q->select('a.id_perfil_puesto',
            'a.id_puesto',
            'a.id_sedearea',
            'b.id_depto',
            'd.nombre',
            DB::raw("(select count(*) from moises.persona_natural_trabajador x where x.id_sedearea=a.id_sedearea and x.id_puesto=a.id_puesto) as cantidad_trab_actual"));
            $q->orderBy('a.id_perfil_puesto', 'asc') ;
            $data = $q->get(); 
            
            $datos=array();
            $nivel++;
            foreach($data as $row){
                $items = array();
                $items['id_perfil_puesto']= $row->id_perfil_puesto;
                $items['id_sedearea']= $row->id_sedearea;
                $items['width']='100px';
                $items['nivel']= $nivel."";
                $items['nombre']= $row->nombre;
                $items['id_puesto']= $row->id_puesto;
                $items['cantidad_trab_actual']= $row->cantidad_trab_actual;
                $items['children']= ConfigurationData::ChildrenPosition($nivel, $id_sedearea,$row->id_perfil_puesto);
                
                $datos[]=$items;
            }
           
            return $datos;
        }
        public static function DeleteProfilePositions($id_perfil_puesto){
            $profilePosition= ProfilePosition::find($id_perfil_puesto);
            if(!empty($profilePosition)){
                
                $rows = ProfilePosition::destroy($id_perfil_puesto);
                if($rows>0){
                    $response=[
                        'success'=> true,
                        'message'=>''
                    ];
                }else{
                    $response=[
                        'success'=> false,
                        'message'=>'No se puede eliminar',
                    ];
                }
    
            }else{
                $response=[
                    'success'=> false,
                    'message'=>'No se ha encontrado grupo escala para eliminar',
                ];
            }
            return $response;
        }
        public static  function ShowListProfilePositions($id_perfil_puesto){
            // dd('xcfdf', $id_perfil_puesto);
            $query = DB::table('plla_perfil_puesto as a')
            ->join('org_sede_area as b', 'a.id_sedearea', '=', 'b.id_sedearea')
            ->join('org_area as c', 'b.id_area', '=',  'c.id_area')
            ->join('plla_puesto as d', 'a.id_puesto', '=', 'd.id_puesto')
            ->where('a.id_perfil_puesto' , '=', $id_perfil_puesto)
            ->select('a.id_perfil_puesto',
            'a.id_tipo_control_personal',
            'a.id_automia_puesto',
            'a.condviajes',
            'a.condmovilizar',
            'a.expinstsimilar',
            'a.exppuestosimilar',
            'a.exp_desde',
            'a.exp_hasta',
            'a.exp_tipo',
            'a.noexperiencia',
            'a.mision',
            'a.id_ubicacionofadm',
            'd.nombre as nombre_puesto',
            'c.nombre as nombre_sede',
             'a.email',
             'a.especificacion',
             'a.id_situacion_educativo',
             'd.id_grupo_compentencia',
            'd.id_grupo_compentencia_org',
            'a.id_sedearea',
            'a.bono',
            'a.id_tipo_horario',
            //  DB::raw("(select count(*) from plla_perfil_puesto j where j.id_perfil_puesto_jefe = a.id_perfil_puesto) as num_personas"),
             DB::raw("(select f.nombre from plla_puesto f, plla_perfil_puesto h where f.id_puesto = h.id_puesto and h.id_perfil_puesto = a.id_perfil_puesto_jefe) as nombre_jefe"),
             DB::raw("FC_MGT_PERSONA_REPORTE(a.id_perfil_puesto) as num_personas"))
            ->get()->shift();
            return $query;
        }
        public static function ListStaffPositions($id_sedearea, $id_anho){
        
            $q = DB::table('plla_perfil_puesto as a');
            $q->join('org_sede_area as b', 'a.id_sedearea', '=', 'b.id_sedearea');
            $q->join('plla_puesto as d', 'a.id_puesto', '=', 'd.id_puesto');
            $q->leftjoin('plla_perfil_puesto_anho as e', 'e.id_perfil_puesto', '=', DB::raw("a.id_perfil_puesto and e.id_anho = ".$id_anho));
            $q->where("a.id_sedearea",$id_sedearea);
            $q->where("a.nivel",0);
            $q->select('a.id_perfil_puesto',
            'a.id_puesto',
            'a.id_sedearea',
            'a.id_situacion_educativo',
            'a.id_tipo_horario',
            'b.id_depto',
            'd.nombre', 
            'd.id_puesto',
            DB::raw("coalesce(e.cantidad, 0) as cantidad"),
            DB::raw("(select count(*) from moises.persona_natural_trabajador x where x.id_sedearea=a.id_sedearea and x.id_puesto=a.id_puesto) as actual"));
            $q->orderBy('a.id_perfil_puesto', 'asc') ;
            $data = $q->get(); 
            
            $datos=array();
            
            foreach($data as $row){
                $items = array();
                $items['id_puesto']= $row->id_puesto;
                $items['id_perfil_puesto']= $row->id_perfil_puesto;
                $items['id_sedearea']= $row->id_sedearea;
                $items['cantidad']= $row->cantidad;
                $items['id_situacion_educativo']= $row->id_situacion_educativo;
                $items['id_tipo_horario']= $row->id_tipo_horario;
                $items['actual']= $row->actual;
                $color = 0;
                if($row->actual > $row->cantidad) {
                    $color = 3;
                } elseif($row->cantidad > 0 and $row->actual == $row->cantidad) {
                    $color = 2;
                } else {
                    if($row->actual > 0 and $row->actual < $row->cantidad){
                        $color = 1;
                    } else {
                    $color = 0;
                }
                }
                $items['color']= $color;
                $items['width']='200px';
                $items['nivel']= '1';
                $items['nombre']= $row->nombre;
                $items['children']=ConfigurationData::ChildrenStaffPosition(1, $id_sedearea,$row->id_perfil_puesto, $id_anho);
                $datos[]=$items;
            }
            
             
            return $datos;

        }
        private static function ChildrenStaffPosition($nivel, $id_sedearea,$id_perfil_puesto, $id_anho){
            $q = DB::table('plla_perfil_puesto as a');
            $q->join('org_sede_area as b', 'a.id_sedearea', '=', 'b.id_sedearea');
            $q->join('plla_puesto as d', 'a.id_puesto', '=', 'd.id_puesto');
            $q->leftjoin('plla_perfil_puesto_anho as e', 'e.id_perfil_puesto', '=', DB::raw("a.id_perfil_puesto and e.id_anho = ".$id_anho));
            $q->where("a.id_sedearea",$id_sedearea);
            $q->where("a.id_perfil_puesto_jefe",$id_perfil_puesto);
            $q->select('a.id_perfil_puesto',
            'a.id_puesto',
            'a.id_sedearea',
            'a.id_situacion_educativo',
            'a.id_tipo_horario',
            'b.id_depto',
            'd.nombre',
            'd.id_puesto',
             DB::raw("coalesce(e.cantidad, 0) as cantidad"),
            DB::raw("(select count(*) from moises.persona_natural_trabajador x where x.id_sedearea=a.id_sedearea and x.id_puesto=a.id_puesto) as actual"));
            $q->orderBy('a.id_perfil_puesto', 'asc') ;
            $data = $q->get(); 
            
            $datos=array();
            $nivel++;
            foreach($data as $row){
                $items = array();
                $items['id_puesto']= $row->id_puesto;
                $items['id_perfil_puesto']= $row->id_perfil_puesto;
                $items['id_sedearea']= $row->id_sedearea;
                $items['cantidad']= $row->cantidad;
                $items['id_situacion_educativo']= $row->id_situacion_educativo;
                $items['id_tipo_horario']= $row->id_tipo_horario;
                $items['actual']= $row->actual;
                $color = 0;
                if($row->actual > $row->cantidad) {
                    $color = 3;
                } elseif($row->cantidad > 0 and $row->actual == $row->cantidad) {
                    $color = 2;
                } else {
                    if($row->actual > 0 and $row->actual < $row->cantidad){
                        $color = 1;
                    }else {
                        $color = 0;
                    }
                }
                $items['color']= $color;
                $items['width']='100px';
                $items['nivel']= $nivel."";
                $items['nombre']= $row->nombre;
                
                $items['children']= ConfigurationData::ChildrenStaffPosition($nivel, $id_sedearea,$row->id_perfil_puesto, $id_anho);
                
                $datos[]=$items;
            }
           
            return $datos;
        }
/// recibiir en array insertar y modificar
        public static  function SaveAfterStaffPosition($request){
            $id_anho=$request->id_anho;
            $detail=$request->detail;
            // dd('gg', $detail );
            ConfigurationData::SaveStaffPosition($id_anho, $detail);
        }

        private static  function SaveStaffPosition($id_anho, $detail){
            
                 foreach($detail as $datos) {
                     $items = (object) $datos;
                   
                    $count = DB::table('plla_perfil_puesto_anho')
                    ->where('id_anho', $id_anho)
                    ->where('id_perfil_puesto', $items->id_perfil_puesto)
                    ->count();
                    $cantidad = 0;
                    if(strlen($items->cantidad) > 0) {
                        $cantidad = $items->cantidad;
                    }
                    if($count == 0) {
                        DB::table('plla_perfil_puesto_anho')->insert(
                            ['id_anho' => $id_anho, 'id_perfil_puesto' => $items->id_perfil_puesto, 'cantidad' => $cantidad]
                        );

                    } else {
                        DB::table('plla_perfil_puesto_anho')
                            ->where('id_anho', $id_anho)
                            ->where('id_perfil_puesto', $items->id_perfil_puesto)
                            ->update(['cantidad' => $cantidad]);
                       
                    }
                    ConfigurationData::SaveStaffPosition($id_anho,  $items->children);
                 }
        }
        //////////////////////////////////////////////////////////////////////////////////////
        public static function saveProfilePositionDatos($request) {

            $id_perfil_puesto=$request->id_perfil_puesto;
            $id_tipo_control_personal=$request->id_tipo_control_personal;
            $email=$request->email;
            $mision=$request->mision;
            $id_ubicacionofadm=$request->id_ubicacionofadm;
            $id_automia_puesto=$request->id_automia_puesto;
            $noexperiencia=$request->noexperiencia;
            $condviajes=$request->condviajes;
            $condmovilizar=$request->condmovilizar;
            $bono=$request->bono;
            $id_tipo_horario=$request->id_tipo_horario;
            $detail=$request->details;
        if ($detail) {
            foreach($detail as $datos) {
                $items = (object) $datos;
                $count = DB::table('plla_perfil_puesto_nivresp')
                            ->where('id_responsabilidad', $items->id_responsabilidad)
                            ->where('id_perfil_puesto', $id_perfil_puesto)
                            ->count();

                if($count == 0) {
                    DB::table('plla_perfil_puesto_nivresp')->insert(
                        ['id_tipo_nivel_resp' => $items->id_tipo_nivel_resp, 'id_responsabilidad' =>  $items->id_responsabilidad, 'id_perfil_puesto' => $id_perfil_puesto]
                    );
                    } else {
                        DB::table('plla_perfil_puesto_nivresp')
                        ->where('id_responsabilidad', $items->id_responsabilidad)
                        ->where('id_perfil_puesto', $id_perfil_puesto)
                        ->update(['id_tipo_nivel_resp' => $items->id_tipo_nivel_resp]);
                   
                    }
            }
        }
                 $result = DB::table('plla_perfil_puesto')
                  ->where('id_perfil_puesto', $id_perfil_puesto)
                  ->update(
                      [
                          'id_tipo_control_personal' => $id_tipo_control_personal,
                          'email' => $email,
                          'mision' => $mision,
                          'id_ubicacionofadm' => $id_ubicacionofadm,
                          'id_automia_puesto' => $id_automia_puesto,
                          'condviajes' => $condviajes,
                          'condmovilizar' => $condmovilizar,
                          'noexperiencia' => $noexperiencia,
                          'bono' => $bono,
                          'id_tipo_horario' => $id_tipo_horario,
                        
                      ]);   
                }

                public static function addResponsabilityes($request){
                    $perfil_puesto_resp = ComunData::correlativo('plla_perfil_puesto_resp', 'perfil_puesto_resp');
                    $id_perfil_puesto=$request->id_perfil_puesto;
                    $descripcion=$request->descripcion;
                    if($perfil_puesto_resp>0){
                        $save = DB::table('plla_perfil_puesto_resp')->insert(
                            [
                            'perfil_puesto_resp' =>  $perfil_puesto_resp,
                            'id_perfil_puesto' => $id_perfil_puesto,
                            'descripcion' => $descripcion,
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
                public static function deleteResponsabilityes($perfil_puesto_resp)
                {
                    
                    $query = DB::table('plla_perfil_puesto_resp')->where('perfil_puesto_resp', $perfil_puesto_resp)->delete();
                    return $query;
                }
                public static function updateResponsability($perfil_puesto_resp, $request) {
                    $descripcion=$request->descripcion;
                    $result = DB::table('plla_perfil_puesto_resp')
                                ->where('perfil_puesto_resp', $perfil_puesto_resp)
                                ->update(['descripcion' => $descripcion]);
                            }

                public static function addFuntions($request){
                    $perfil_puesto_func = ComunData::correlativo('plla_perfil_puesto_func', 'perfil_puesto_func');
                    $id_perfil_puesto=$request->id_perfil_puesto;
                    $descripcion=$request->descripcion;
                    $puntaje=$request->puntaje;
                    $frecuencia=$request->frecuencia;
                    if($perfil_puesto_func>0){
                        $save = DB::table('plla_perfil_puesto_func')->insert(
                            [
                            'perfil_puesto_func' =>  $perfil_puesto_func,
                            'id_perfil_puesto' => $id_perfil_puesto,
                            'descripcion' => $descripcion,
                            'puntaje' => $puntaje,
                            'frecuencia' => $frecuencia,
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
                public static function deleteFuntions($perfil_puesto_func)
                {
                    
                    $query = DB::table('plla_perfil_puesto_func')->where('perfil_puesto_func', $perfil_puesto_func)->delete();
                    return $query;
                }
                public static function updateFuntions($perfil_puesto_func, $request) {
                    $descripcion=$request->descripcion;
                    $puntaje=$request->puntaje;
                    $frecuencia=$request->frecuencia;
                    $result = DB::table('plla_perfil_puesto_func')
                                ->where('perfil_puesto_func', $perfil_puesto_func)
                                ->update(['descripcion' => $descripcion,
                                          'puntaje' => $puntaje,
                                          'frecuencia' => $frecuencia,
                                          ]);
                }
                public static  function listResponsabilityes($id_perfil_puesto){
                    // dd('xcfdf');
                    $query= DB::table('plla_perfil_puesto_resp')
                    ->where("id_perfil_puesto", '=', $id_perfil_puesto)
                    ->select('perfil_puesto_resp',
                    'descripcion')
                    ->get();
                    // $position = Position::find($id_puesto);
                    return $query;
                }
                public static  function listFuntions($id_perfil_puesto){
                    // dd('xcfdf');
                    $query= DB::table('plla_perfil_puesto_func')
                    ->where("id_perfil_puesto", '=', $id_perfil_puesto)
                    ->select('perfil_puesto_func',
                    'descripcion',
                    'puntaje',
                    'frecuencia',
                    DB::raw("(CASE WHEN FRECUENCIA = 'M' THEN 'Mensual' WHEN FRECUENCIA = 'S' THEN 'Semanal' WHEN FRECUENCIA = 'D' THEN 'Diario' ELSE '' END) as frecuencias"))
                    ->get();
                    // $position = Position::find($id_puesto);
                    return $query;
                }

                public static function updateSituationEducation($id_perfil_puesto, $request) {
                    // dd('sss', $request);
                    
                    $especificacion=$request->especificacion;
                    $id_situacion_educativo=$request->id_situacion_educativo;
                   
                    $result = DB::table('plla_perfil_puesto')
                                ->where('id_perfil_puesto', $id_perfil_puesto)
                                ->update(['especificacion' => $especificacion, 'id_situacion_educativo' => $id_situacion_educativo]);
                            }

                public static function addDiplomations($request){
     
                    $perfil_puesto_espdip = ComunData::correlativo('plla_perfil_puesto_espdip', 'perfil_puesto_espdip');
                    $id_perfil_puesto=$request->id_perfil_puesto;
                    $descripcion=$request->descripcion;
                    $id_tipo_espdip=$request->id_tipo_espdip;
                    if($perfil_puesto_espdip>0){
                        $save = DB::table('plla_perfil_puesto_espdip')->insert(
                            [
                            'perfil_puesto_espdip' =>  $perfil_puesto_espdip,
                            'id_perfil_puesto' => $id_perfil_puesto,
                            'descripcion' => $descripcion,
                            'id_tipo_espdip' => $id_tipo_espdip,
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

                public static  function listDiplomations($id_perfil_puesto){
                    // dd('xcfdf', $id_perfil_puesto);
                    $query= DB::table('plla_perfil_puesto_espdip as a')
                    ->join('plla_tipo_espdip as b', 'a.id_tipo_espdip', '=', 'b.id_tipo_espdip')
                    ->where("a.id_perfil_puesto", '=', $id_perfil_puesto)
                    ->select('a.perfil_puesto_espdip',
                    'a.descripcion',
                    'b.nombre')
                    ->get();
                    // $position = Position::find($id_puesto);
                    // SELECT A.PERFIL_PUESTO_ESPDIP, A.DESCRIPCION, B.NOMBRE FROM PLLA_PERFIL_PUESTO_ESPDIP A, PLLA_TIPO_ESPDIP B WHERE A.ID_TIPO_ESPDIP = B.ID_TIPO_ESPDIP AND A.ID_PERFIL_PUESTO=37;
                    return $query;
                }
                public static function deleteDiplomations($perfil_puesto_espdip)
                {
                    
                    $query = DB::table('plla_perfil_puesto_espdip')->where('perfil_puesto_espdip', $perfil_puesto_espdip)->delete();
                    return $query;
                }
                public static function addProfesionOcupation($request){
                   
                    $id_perfil_puesto=$request->id_perfil_puesto;
                    $id_profesion=$request->id_profesion;
                    $count = DB::table('plla_perfil_puesto_prof')
                            ->where('id_perfil_puesto', $id_perfil_puesto)
                            ->where('id_profesion', $id_profesion)
                            ->count();

                if($count == 0) {
                    $id_perfil_puesto_prof = ComunData::correlativo('plla_perfil_puesto_prof', 'id_perfil_puesto_prof');
                    if($id_perfil_puesto_prof>0){
                        
                        $save = DB::table('plla_perfil_puesto_prof')->insert(
                            [
                            'id_perfil_puesto_prof' =>  $id_perfil_puesto_prof,
                            'id_perfil_puesto' => $id_perfil_puesto,
                            'id_profesion' => $id_profesion,
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
                         
                    } else{
                            $response=[
                                'success'=> false,
                                'message'=>'No se ha generado correlativo',
                            ];
                        } 
                    } else{
                            $response=[
                                'success'=> false,
                                'message'=>'El item ya existe',
                            ];
                        } 
                
                return $response;
            }
                public static  function listProfesionOcupation($id_perfil_puesto){
                    // dd('xcfdf', $id_perfil_puesto);
                    $query= DB::table('plla_perfil_puesto_prof as a')
                    ->join('moises.profesiones as b', 'a.id_profesion', '=', 'b.id_profesion')
                    ->where("a.id_perfil_puesto", '=', $id_perfil_puesto)
                    ->select('a.id_perfil_puesto_prof',
                    'a.id_profesion',
                    'b.nombre')
                    ->get();
                    //   dd('xcfdf', $query);
                    return $query;
                }
                public static function deletProfesionOcupation($id_perfil_puesto_prof)
                {
                    
                    $query = DB::table('plla_perfil_puesto_prof')->where('id_perfil_puesto_prof', $id_perfil_puesto_prof)->delete();
                    return $query;
                }

                public static function updateExperence($id_perfil_puesto, $request) {
                    $expinstsimilar=$request->expinstsimilar;
                    $exppuestosimilar=$request->exppuestosimilar;
                    $exp_desde=$request->exp_desde;
                    $exp_hasta=$request->exp_hasta;
                    $exp_tipo=$request->exp_tipo;
                    $result = DB::table('plla_perfil_puesto')
                    ->where('id_perfil_puesto', $id_perfil_puesto)
                    ->update(['expinstsimilar' => $expinstsimilar, 'exppuestosimilar' => $exppuestosimilar, 'exp_desde' => $exp_desde, 'exp_hasta' => $exp_hasta, 'exp_tipo' => $exp_tipo]);   
                }
                public static function addLenguagesLevel($request){
                    $id_perfil_puesto=$request->id_perfil_puesto;
                    $id_tipoidioma=$request->id_tipoidioma;
                    $id_tipo_nivel=$request->id_tipo_nivel;

                    $count = DB::table('plla_perfil_puesto_idioma')
                            ->where('id_perfil_puesto', $id_perfil_puesto)
                            ->where('id_tipoidioma', $id_tipoidioma)
                            ->count();

                    if($count == 0) {
                        $save =  DB::table('plla_perfil_puesto_idioma')
                                    ->insert(['id_perfil_puesto' => $id_perfil_puesto, 'id_tipoidioma' => $id_tipoidioma, 'id_tipo_nivel' => $id_tipo_nivel]);
                        
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
                        } else{
                            $response=[
                                'success'=> false,
                                'message'=>'El item ya existe',
                            ];
                        }
                        return $response;
                }
                public static  function listLenguagesLevel($id_perfil_puesto){
               
                    $query= DB::table('plla_perfil_puesto_idioma as a')
                    ->join('moises.tipo_idioma as b', 'a.id_tipoidioma', '=', 'b.id_tipoidioma')
                    ->join('plla_tipo_nivel as c', 'a.id_tipo_nivel', '=', 'c.id_tipo_nivel')
                    ->where('a.id_perfil_puesto', $id_perfil_puesto)
                    ->select('a.id_tipoidioma', DB::raw(" b.nombre idioma, c.nombre nivel"))
                    ->get();
                    // dd('xcfdf', $query);
                    return $query;
                }
                public static function deleteLenguagesLevel($id_tipoidioma)
                {
                    
                    $query = DB::table('plla_perfil_puesto_idioma')->where('id_tipoidioma', $id_tipoidioma)->delete();
                    return $query;
                }
                public static function addOffimaticaLevel($request){
                    $id_perfil_puesto=$request->id_perfil_puesto;
                    $id_conoci_inform=$request->id_conoci_inform;
                    $id_tipo_nivel=$request->id_tipo_nivel;

                    $count = DB::table('plla_perfil_puesto_inform')
                            ->where('id_perfil_puesto', $id_perfil_puesto)
                            ->where('id_conoci_inform', $id_conoci_inform)
                            ->count();

                    if($count == 0) {
                        $save =  DB::table('plla_perfil_puesto_inform')
                                    ->insert(['id_perfil_puesto' => $id_perfil_puesto, 'id_conoci_inform' => $id_conoci_inform, 'id_tipo_nivel' => $id_tipo_nivel]);
                        
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
                        } else{
                            $response=[
                                'success'=> false,
                                'message'=>'El item ya existe',
                            ];
                        }
                        return $response;
                }
                public static function listOffimaticaLevel($id_perfil_puesto) {
                    $query = DB::table('plla_perfil_puesto_inform as a')
                    ->join('plla_conoci_inform as b', 'a.id_conoci_inform', '=', 'b.id_conoci_inform')
                    ->join('plla_tipo_nivel as c', 'a.id_tipo_nivel', '=', 'c.id_tipo_nivel')
                    ->where('id_perfil_puesto', $id_perfil_puesto)
                    ->select('a.id_conoci_inform', DB::raw(" b.nombre nombre_offimatica, c.nombre nivel"))
                    ->get();
                    return $query;
                }
                public static function deleteOffimaticaLevel($id_conoci_inform)
                {
                    
                    $query = DB::table('plla_perfil_puesto_inform')->where('id_conoci_inform', $id_conoci_inform)->delete();
                    return $query;
                }
                public static function addRequiremnts($request){
                    $id_perfil_puesto=$request->id_perfil_puesto;
                    $id_requisitos=$request->id_requisitos;
                    $tipo=$request->tipo;

                    $count = DB::table('plla_perfil_puesto_requi')
                            ->where('id_perfil_puesto', $id_perfil_puesto)
                            ->where('id_requisitos', $id_requisitos)
                            ->count();

                    if($count == 0) {
                        $save =  DB::table('plla_perfil_puesto_requi')
                                    ->insert(['id_perfil_puesto' => $id_perfil_puesto, 'id_requisitos' => $id_requisitos, 'tipo' => $tipo]);
                        
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
                        } else{
                            $response=[
                                'success'=> false,
                                'message'=>'El item ya existe',
                            ];
                        }
                        return $response;
                }
                public static function listRequiremnts($id_perfil_puesto) {
                    $query = DB::table('plla_perfil_puesto_requi as a')
                    ->join('plla_requisitos as b', 'a.id_requisitos', '=', 'b.id_requisitos')
                    ->where('id_perfil_puesto', $id_perfil_puesto)
                    ->select('a.id_requisitos', 'b.nombre',
                    DB::raw("(CASE WHEN TIPO = 'A' THEN 'Al Postular' WHEN TIPO = 'I' THEN 'Ingreso al Trabajo' ELSE 'Ambos' END) as tipo"))
                    ->get();
                    return $query;
                }
                public static function deleteRequiremnts($id_requisitos) {
                    $query = DB::table('plla_perfil_puesto_requi')->where('id_requisitos', $id_requisitos)->delete();
                    return $query;
                }

                public static function addGroupCompetences($request){
    //  dd($request);
                    $id_grupo_compentencia = ComunData::correlativo('plla_grupo_compentencia', 'id_grupo_compentencia');
                    $nombre=$request->nombre;
                    $tipo=$request->tipo;
                    $vigencia=$request->vigencia;
                    if($id_grupo_compentencia>0){
                        $save = DB::table('plla_grupo_compentencia')->insert(
                            [
                            'id_grupo_compentencia' =>  $id_grupo_compentencia,
                            'nombre' => $nombre,
                            'tipo' => $tipo,
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
                public static function listGroupCompetences() {
                    $query = DB::table('plla_grupo_compentencia as a')
                    ->where('a.vigencia', 1)
                    ->select('a.id_grupo_compentencia', 'a.nombre', 'a.tipo as tipo_prefijo',
                    DB::raw("(CASE WHEN a.tipo = 'O' THEN 'ORGANIZACIONAL' WHEN a.tipo = 'F' THEN 'FUNCIONAL' ELSE '' END) as tipo"))
                    ->orderBy('a.nombre', 'asc')
                    ->get();
                    return $query;
                }
                public static function deleteGroupCompetences($id_grupo_compentencia) {
                    $query = DB::table('plla_grupo_compentencia')->where('id_grupo_compentencia', $id_grupo_compentencia)->delete();
                    return $query;
                }
                public static function updateGroupCompetences($id_grupo_compentencia, $request) {
                    $nombre=$request->nombre;
                    $tipo=$request->tipo;
                    $result = DB::table('plla_grupo_compentencia')
                    ->where('id_grupo_compentencia', $id_grupo_compentencia)
                    ->update(['nombre' => $nombre, 'tipo' => $tipo]); 
                }
                
                public static  function addCompetencesGroup($request){
                    $id_grupo_compentencia=$request->id_grupo_compentencia;
                    $detail=$request->detail;
                    $puntaje = ConfigurationData::sumaCompetenciaNivel($id_grupo_compentencia,0);
                    
                    foreach ($detail as  $datos) {
                        $items =(object)$datos;
                        if($items->checked == true){
                            $pje = $items->puntaje;
                            if(strlen($items->puntaje)==0){
                                $pje = 0;
                            }
                            $puntaje=$puntaje + $pje;
                        }
                    }
                    
                    if($puntaje>100){
                        $response=[
                            'success'=> false,
                            'message'=>'Puntaje mayor a 100',
                        ];
                        return $response;
                    }
                    $nli = [];
                    $pk =  ComunData::correlativo('plla_grupo_compentencia_nivel', 'id_grupo_compentencia_nivel');
                    foreach ($detail as  $datos) {
                            $items =(object)$datos;
                            if($items->checked == true){
                            $pje = $items->puntaje;
                            if(strlen($items->puntaje)==0){
                                $pje = 0;
                            }
                            $nv =  ['id_grupo_compentencia_nivel' => $pk,
                                'id_grupo_compentencia' => $id_grupo_compentencia,
                                'id_competencia' =>  $items->id_competencia,
                                'id_tipo_nivel_comp' =>  $items->id_tipo_nivel_comp,
                                'puntaje' =>  $pje,
                            ];
                            
                            $pk = $pk + 1;
                            array_push($nli, $nv);
                        }
                    }
                    
                    
                    $inserted = DB::table('plla_grupo_compentencia_nivel')->insert($nli);
                    if($inserted){
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
                    return $response;
                }
                private static function  sumaCompetenciaNivel($id_grupo_compentencia,$id_grupo_compentencia_nivel){
                    $total = DB::table('plla_grupo_compentencia_nivel')
                            ->where('id_grupo_compentencia',$id_grupo_compentencia)
                            ->where('id_grupo_compentencia_nivel','<>',$id_grupo_compentencia_nivel)
                            ->sum('puntaje');
                    if(empty($total)){
                        $total=0;
                    }
                    
                    return $total;
                    
                }
                public static function listCompetencesGroup($id_grupo_compentencia) {
                    $query = DB::table('plla_grupo_compentencia_nivel as a')
                    ->join('plla_grupo_compentencia as b', 'a.id_grupo_compentencia', '=', 'b.id_grupo_compentencia')
                    ->join('plla_competencia as c', 'a.id_competencia', '=', 'c.id_competencia')
                    ->join('plla_tipo_nivel_comp as d', 'a.id_tipo_nivel_comp', '=', 'd.id_tipo_nivel_comp')
                    ->where('a.id_grupo_compentencia', $id_grupo_compentencia)
                    ->select('a.id_grupo_compentencia_nivel', 'b.id_grupo_compentencia', 'c.nombre', 'd.nombre as tipo_competencia', 'd.id_tipo_nivel_comp',
                    DB::raw("(CASE WHEN C.TIPO = 'O' THEN 'ORGANIZACIONAL' WHEN C.TIPO = 'F' THEN 'FUNCIONAL' ELSE '' END) comp_tipo"),
                    'a.puntaje', 'c.id_competencia')
                    ->orderBy('a.id_grupo_compentencia_nivel', 'asc')
                    ->get();
                    return $query;
                }
             
                public static function deleteCompetencesGroup($id_grupo_compentencia_nivel) {
                    $query = DB::table('plla_grupo_compentencia_nivel')->where('id_grupo_compentencia_nivel', $id_grupo_compentencia_nivel)->delete();
                    return $query;
                }
                public static function updateCompetencesGroup($id_grupo_compentencia_nivel, $request) {
                    
                    $puntajetotal = ConfigurationData::sumaCompetenciaNivel($request->id_grupo_compentencia,$id_grupo_compentencia_nivel);
                    
                    $id_tipo_nivel_comp=$request->id_tipo_nivel_comp;
                    $puntaje=$request->puntaje;
                    
                    $puntajetotal= $puntajetotal + $puntaje;
                    
                    if($puntajetotal>100){
                        $response=[
                            'success'=> false,
                            'message'=>'Puntaje mayor a 100',
                        ];
                        return $response;
                    }
                    
                    $result = DB::table('plla_grupo_compentencia_nivel')
                    ->where('id_grupo_compentencia_nivel', $id_grupo_compentencia_nivel)
                    ->update(['id_tipo_nivel_comp' => $id_tipo_nivel_comp, 'puntaje' => $puntaje]); 
                    $response=[
                            'success'=> true,
                            'message'=>'',
                        ];
                    
                    return $response;
                    
                }
                public static function addCompetenciasLb($request){
                    //  dd($request);
                    $id_competencia = ComunData::correlativo('plla_competencia', 'id_competencia');
                    $nombre=$request->nombre;
                    $tipo=$request->tipo;
                    $vigencia=$request->vigencia;
                    if($id_competencia>0){
                        $save = DB::table('plla_competencia')->insert(
                            [
                            'id_competencia' =>  $id_competencia,
                            'nombre' => $nombre,
                            'tipo' => $tipo,
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
            public static function deleteCompetenciasLb($id_competencia) {
                // dd('hola', $id_competencia);
                $query = DB::table('plla_competencia')->where('id_competencia', $id_competencia)->delete();
                return $query;
            }
            public static function updateCompetenciasLb($id_competencia, $request) {
                $nombre=$request->nombre;
                $tipo=$request->tipo;
                $result = DB::table('plla_competencia')
                ->where('id_competencia', $id_competencia)
                ->update(['nombre' => $nombre, 'tipo' => $tipo]); 
            }
            public static function addComitions($request){
                   
                $id_perfil_puesto=$request->id_perfil_puesto;
                $id_comisiones=$request->id_comisiones;
                $count = DB::table('plla_perfil_puesto_comis_dir')
                        ->where('id_perfil_puesto', $id_perfil_puesto)
                        ->where('id_comisiones', $id_comisiones)
                        ->count();

            if($count == 0) {
                $id_perfil_puesto_comis_dir = ComunData::correlativo('plla_perfil_puesto_comis_dir', 'id_perfil_puesto_comis_dir');
                if($id_perfil_puesto_comis_dir>0){
                    
                    $save = DB::table('plla_perfil_puesto_comis_dir')->insert(
                        [
                        'id_perfil_puesto_comis_dir' =>  $id_perfil_puesto_comis_dir,
                        'id_comisiones' =>  $id_comisiones,
                        'id_perfil_puesto' => $id_perfil_puesto,
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
                     
                } else{
                        $response=[
                            'success'=> false,
                            'message'=>'No se ha generado correlativo',
                        ];
                    } 
                } else{
                        $response=[
                            'success'=> false,
                            'message'=>'El item ya existe',
                        ];
                    } 
            
            return $response;
        }
            public static  function listComitions($id_perfil_puesto){
                // dd('xcfdf', $id_perfil_puesto);
                $query= DB::table('plla_perfil_puesto_comis_dir as a')
                ->join('plla_comisiones as b', 'a.id_comisiones', '=', 'b.id_comisiones')
                ->where("a.id_perfil_puesto", '=', $id_perfil_puesto)
                ->select('a.id_perfil_puesto_comis_dir',
                'a.id_perfil_puesto',
                'a.id_comisiones',
                'b.nombre',
                'b.descripcion')
                ->get();
                //   dd('xcfdf', $query);
                return $query;
            }
            public static function deleteComitions($id_perfil_puesto_comis_dir)
            {
                
                $query = DB::table('plla_perfil_puesto_comis_dir')->where('id_perfil_puesto_comis_dir', $id_perfil_puesto_comis_dir)->delete();
                return $query;
            }
            public static function saveProcess($request){
                    //    dd($request);
                $id_perfil_puesto=$request->id_perfil_puesto;
                $id_procesos=$request->id_procesos;
                $count = DB::table('plla_perfil_puesto_proc')
                        ->where('id_perfil_puesto', $id_perfil_puesto)
                        ->where('id_procesos', $id_procesos)
                        ->count();
            
            if($count == 0) {
                $id_perfil_puesto_proc = ComunData::correlativo('plla_perfil_puesto_proc', 'id_perfil_puesto_proc');
                // dd('s',  $id_perfil_puesto_proc,  $id_procesos);
                if($id_perfil_puesto_proc>0){
                    
                    $save = DB::table('plla_perfil_puesto_proc')->insert(
                        [
                        'id_perfil_puesto_proc' =>  $id_perfil_puesto_proc,
                        'id_procesos' =>  $id_procesos,
                        'id_perfil_puesto' => $id_perfil_puesto,
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
                 
                } else{
                        $response=[
                            'success'=> false,
                            'message'=>'No se ha generado correlativo',
                        ];
                    } 
                } else{
                        $response=[
                            'success'=> false,
                            'message'=>'El item ya existe',
                        ];
                    } 
                
                 return $response;
    }
                public static  function listProcess($id_perfil_puesto){
                    // dd('xcfdf', $id_perfil_puesto);
                    $query= DB::table('plla_perfil_puesto_proc as a')
                    ->join('plla_procesos as b', 'a.id_procesos', '=', 'b.id_procesos')
                    ->where("a.id_perfil_puesto", '=', $id_perfil_puesto)
                    ->select('a.id_perfil_puesto_proc',
                    'a.id_perfil_puesto',
                    'a.id_procesos',
                    'b.nombre',
                    'b.descripcion')
                    ->get();
                    //   dd('xcfdf', $query);
                    return $query;
                }
                public static function deleteProcess($id_perfil_puesto_proc)
                {

                    $query = DB::table('plla_perfil_puesto_proc')->where('id_perfil_puesto_proc', $id_perfil_puesto_proc)->delete();
                    return $query;
                }
                public static function saveJefeFun($request){
                    //    dd($request);
                $id_perfil_puesto=$request->id_perfil_puesto;
                $id_puesto_puesto_jefe=$request->id_puesto_puesto_jefe;
                $count = DB::table('plla_perfil_puesto_jefe_fun')
                        ->where('id_perfil_puesto', $id_perfil_puesto)
                        ->where('id_puesto_puesto_jefe', $id_puesto_puesto_jefe)
                        ->count();

                    if($count == 0) {
                        $id_perfil_puesto_jefe_fun = ComunData::correlativo('plla_perfil_puesto_jefe_fun', 'id_perfil_puesto_jefe_fun');
                        // dd('s',  $id_perfil_puesto_jefe_fun,  $id_procesos);
                        if($id_perfil_puesto_jefe_fun>0){

                            $save = DB::table('plla_perfil_puesto_jefe_fun')->insert(
                                [
                                'id_perfil_puesto_jefe_fun' =>  $id_perfil_puesto_jefe_fun,
                                'id_puesto_puesto_jefe' =>  $id_puesto_puesto_jefe,
                                'id_perfil_puesto' => $id_perfil_puesto,
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
                                    
                        } else{
                                $response=[
                                    'success'=> false,
                                    'message'=>'No se ha generado correlativo',
                                ];
                            } 
                        } else{
                                $response=[
                                    'success'=> false,
                                    'message'=>'El item ya existe',
                                ];
                            } 
                        
                    return $response;
                    }
                public static  function listJefeFun($id_perfil_puesto){
                    // dd('xcfdf', $id_perfil_puesto);
                    $query= DB::table('plla_perfil_puesto_jefe_fun as a')
                    ->join('plla_perfil_puesto as b', 'a.id_puesto_puesto_jefe', '=', 'b.id_perfil_puesto')
                    ->join('plla_puesto as c', 'b.id_puesto', '=', 'c.id_puesto')
                    ->join('org_sede_area as d', 'b.id_sedearea', '=', 'd.id_sedearea')
                    ->join('org_area as e', 'd.id_area', '=', 'e.id_area')
                    ->where("a.id_perfil_puesto", '=', $id_perfil_puesto)
                    ->select('a.id_perfil_puesto_jefe_fun',
                    'a.id_perfil_puesto',
                    'c.nombre',
                    'd.id_depto',
                    'e.nombre as nombre_depto')
                    ->get();
                    //   dd('xcfdf', $query);
                    return $query;
                }
                public static function deleteJefeFun($id_perfil_puesto_jefe_fun)
                {
                    
                    $query = DB::table('plla_perfil_puesto_jefe_fun')->where('id_perfil_puesto_jefe_fun', $id_perfil_puesto_jefe_fun)->delete();
                    return $query;
                }
                public static function saveSuperFunc($request){
                    //    dd($request);
                $id_perfil_puesto=$request->id_perfil_puesto;
                $id_perfil_puesto_fun=$request->id_perfil_puesto_fun;
                $count = DB::table('plla_perfil_puesto_sup_fun')
                        ->where('id_perfil_puesto', $id_perfil_puesto)
                        ->where('id_perfil_puesto_fun', $id_perfil_puesto_fun)
                        ->count();
                
                if($count == 0) {
                $id_perfil_puesto_sup_fun = ComunData::correlativo('plla_perfil_puesto_sup_fun', 'id_perfil_puesto_sup_fun');
                // dd('s',  $id_perfil_puesto_sup_fun,  $id_procesos);
                if($id_perfil_puesto_sup_fun>0){

                    $save = DB::table('plla_perfil_puesto_sup_fun')->insert(
                        [
                        'id_perfil_puesto_sup_fun' =>  $id_perfil_puesto_sup_fun,
                        'id_perfil_puesto_fun' =>  $id_perfil_puesto_fun,
                        'id_perfil_puesto' => $id_perfil_puesto,
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
                            
                } else{
                     $response=[
                         'success'=> false,
                         'message'=>'No se ha generado correlativo',
                     ];
                } 
                } else{
                        $response=[
                            'success'=> false,
                            'message'=>'El item ya existe',
                        ];
                    } 
                
                return $response;
                }
            
                public static  function listSuperFunc($id_perfil_puesto){
                    // dd('xcfdf', $id_perfil_puesto);
                    $query= DB::table('plla_perfil_puesto_sup_fun as a')
                    ->join('plla_perfil_puesto as b', 'a.id_perfil_puesto_fun', '=', 'b.id_perfil_puesto')
                    ->join('plla_puesto as c', 'b.id_puesto', '=', 'c.id_puesto')
                    ->join('org_sede_area as d', 'b.id_sedearea', '=', 'd.id_sedearea')
                    ->join('org_area as e', 'd.id_area', '=', 'e.id_area')
                    ->where("a.id_perfil_puesto", '=', $id_perfil_puesto)
                    ->select('a.id_perfil_puesto_sup_fun',
                    'a.id_perfil_puesto',
                    'c.nombre',
                    'd.id_depto',
                    'e.nombre as nombre_depto')
                    ->get();
                    //   dd('xcfdf', $query);
                    return $query;
                }
            
                public static function deleteSuperFunc($id_perfil_puesto_sup_fun)
                {
                    
                    $query = DB::table('plla_perfil_puesto_sup_fun')->where('id_perfil_puesto_sup_fun', $id_perfil_puesto_sup_fun)->delete();
                    return $query;
                }
                
}
