<?php
namespace App\Http\Data\Financial;

use App\Models\Criterion;
use Illuminate\Support\Facades\DB;

class CriterionData {  



    /**
     * name="criterion",
     * description="Obtiene criterios segun un nivel de enseñanza y un filtro de nombre de criterio.",
     */
    protected static function criterion($id_nivel_ensenanza, $nombre){

        return Criterion::with([
//            'teachingLevel:id_nivel_ensenanza,nombre',
            'parent:id_criterio,nombre',
            'affects:id_criterio,nombre'])
            ->select(
                'id_criterio',
                'nombre',
                'codigo',
                'id_parent',
                'id_afecta',
//                'id_nivel_ensenanza',
                'tipo_cobro',
                'dc',
                'estado',
//                'tipo_asignacion',
                'orden',
                'id_modo_contrato',
                'tipo_dscto',
                'ver_hijo'
                )
//                ->where('id_nivel_ensenanza','=',$id_nivel_ensenanza)
                ->whereRaw("UPPER(nombre) like UPPER('%$nombre%')")
                ->orderBy('dc', 'desc')
                ->orderBy('orden', 'desc')
                ->paginate(15);
    }


    /**
     * name="criterionList",
     * description="Obtiene criterios con informacion basica, segun un nivel de enseñanza.",
     */
    protected static function criterionList($params){
//        dd($params);


        $q =  Criterion::select(
                'id_criterio',
                'nombre',
                'codigo',
                'id_parent',
                'id_afecta',
                'estado',
                'orden',
                'tipo_cobro',
                'dc',
                'id_criterio_proc',
                'id_modo_contrato',
                'tipo_dscto',
                'ver_hijo'

            );
        if ($params->has('criteries_semester_filter') && $params->id_semestre_programa) { // esto filtra programas que no estan registrados en plan de pagos
//            $lista = self::CriteriaSemesterExist($params);
//                dd($lista);
            $q = $q->whereNotIn('id_criterio', self::CriteriaSemesterExistCont($params));
        }
        if ($params->has('dc')){
            if($params->dc == 'D'){
                $q = $q->where('dc', $params->dc);
                if($params->has('origen')){
                    if($params->origen == 'parent'){
                        $q = $q->whereNull('id_parent');
                    }
                }
            }
            if($params->dc == 'C'){
                if($params->has('origen')){
                    if($params->origen == 'parent'){
                        $q = $q->whereNull('id_parent');
                        $q = $q->where('dc', $params->dc);
                    }
                    if($params->origen == 'afecta'){
                        $q = $q->where('dc', 'D');
                    }
                }else {
                    $q = $q->where('dc', $params->dc);
                }

            }
        }


        if($params->has('codigo') && $params->codigo){
            $q = $q->whereIn('codigo', explode(',', $params->codigo));
        }
        if($params->has('nombre') && $params->nombre){
            $q = $q->whereRaw("UPPER(nombre) like UPPER('%$params->nombre%')");
        }

        return $q->orderBy('dc', 'desc')->orderBy('orden', 'asc')->get();
    }
    protected static function criterionLineal($params){
//        dd($params);
        $q =  DB::table('MAT_CRITERIO as A')->select(
                'A.id_criterio',
                'A.nombre',
                'A.codigo',
                'A.id_parent',
//                'A.id_afecta',
//                'A.estado',
                'A.orden',
//                'A.tipo_cobro',
//                'A.id_tipo_requisito_beca',
//                'A.tipo',
                'A.dc',
                'A.estado',
                'B.nombre as afecta',
                'C.nombre as tipo_beca',
                'A.comentario',
                'A.tipo_dscto',
                'A.id_modo_contrato',
                'X.nombre as criterio_proc',
                'f.nombre as tipodscto',
                'A.ver_hijo',
            DB::raw("
            DECODE(A.tipo, 'E', 'Enseñanza', 'M', 'Matricula','R', 'Residencia') AS tipo,
            DECODE(A.tipo_cobro, 'M', 'Mensual', 'U', 'Único') AS tipo_cobro, 
            DECODE(A.tipo_alumno, 'RE', 'Regular', 'B18', 'Beca 18') AS tipo_alumno,
            case when A.ver_hijo = 'S' then 'Si' when A.ver_hijo = 'N' then 'No' else '' end AS ver_hijo_desc")
            
            )
            ->leftJoin('MAT_CRITERIO as B', 'B.ID_CRITERIO', '=', 'A.ID_AFECTA')
            ->leftJoin('DAVID.TIPO_REQUISITO_BECA as C', 'A.ID_TIPO_REQUISITO_BECA', '=', 'C.ID_TIPO_REQUISITO_BECA')
            ->leftJoin('MAT_CRITERIO as X', 'X.ID_CRITERIO', '=', 'A.ID_CRITERIO_PROC')
            ->leftJoin('FIN_TIPO_DSCTO as f', 'f.TIPO_DSCTO', '=', 'A.TIPO_DSCTO')
            ->whereRaw("a.dc like '%".$params->dc."%'");
            if($params->has('id_modo_contrato') && $params->id_modo_contrato){
                $q->where('A.id_modo_contrato',$params->id_modo_contrato);
            }
        if($params->has('nombre') && $params->nombre){
            $q = $q->whereRaw("UPPER(A.nombre) like UPPER('%$params->nombre%')");
        }

//            ->where('id_nivel_ensenanza','=',$id_nivel_ensenanza)
            return $q->orderBy('A.dc', 'desc')->orderBy('A.orden', 'asc')->get();
    }

    private static function CriteriaSemesterExist($params){
//        dd($params->id_semestre_programa);
        return collect(DB::select("select 
                        MAT_CRITERIO.ID_CRITERIO
                        from MAT_CRITERIO_SEMESTRE
                        join MAT_CRITERIO on MAT_CRITERIO_SEMESTRE.ID_CRITERIO = MAT_CRITERIO.ID_CRITERIO
                        where 
                        -- MAT_CRITERIO.ID_NIVEL_ENSENANZA = $params->id_nivel_ensenanza
                        MAT_CRITERIO_SEMESTRE.ID_SEMESTRE_PROGRAMA = $params->id_semestre_programa"))
            ->pluck('id_criterio')->toArray();
    }
    private static function CriteriaSemesterExistCont($params){
//        dd($params->id_semestre_programa);
        return collect(DB::select("select 
                        cs.ID_CRITERIO
                        from VW_MAT_CRITERIO_SEMESTRE cs
                        where cs.ID_SEMESTRE_PROGRAMA = ".$params->id_semestre_programa."
                        and cs.ID_CRITERIO NOT in(
                            select coalesce(ID_PARENT,0)  from MAT_CRITERIO
                            where ID_CRITERIO not in(
                                select ID_CRITERIO from MAT_CRITERIO_SEMESTRE
                                where ID_SEMESTRE_PROGRAMA = ".$params->id_semestre_programa."
                            )   
                        )"
                        ))
            ->pluck('id_criterio')->toArray();
    }


    public static function criterionListCriterieSemestre($id_nivel_ensenanza, $id_semestre_programa){
//        dd('getting');
        if($id_semestre_programa){
            $exist = collect(DB::select("select MAT_CRITERIO.ID_CRITERIO
                        from MAT_CRITERIO_SEMESTRE
                                 join MAT_CRITERIO on MAT_CRITERIO_SEMESTRE.ID_CRITERIO = MAT_CRITERIO.ID_CRITERIO
                        where 
                        -- MAT_CRITERIO.ID_NIVEL_ENSENANZA = $id_nivel_ensenanza
                        MAT_CRITERIO_SEMESTRE.ID_SEMESTRE_PROGRAMA = $id_semestre_programa"))
            ->pluck('id_criterio')->toArray();
        }
        $respo =  Criterion::select(
                'id_criterio',
                'nombre',
                'codigo',
                'id_parent',
                'id_afecta',
                'estado',
                'orden',
                'ver_hijo'
            );
//            ->where('id_nivel_ensenanza','=',$id_nivel_ensenanza);
        if($id_semestre_programa){
            $respo = $respo->whereNotIn('id_criterio', $exist);
        }
            return $respo->get();
    }
    public static function getListCriteriaAfecta($params) {

        $tipo = $params->tipo;

        if($tipo=='S'){
            $parametro = $params->data;
        }else{
            $query="select id_criterio, nombre, codigo, id_parent, id_afecta, estado, dc, orden
                    from eliseo.vw_mat_criterio
                    where id_criterio not in(select id_criterio from mat_criterio_semestre where id_semestre_programa=".$params->id_semestre_programa.")
                    and dc='C'
                    and id_afecta in(".$params->data.")
                    and estado='1'
                    order by orden
                    ";
            $rdata = DB::select($query);
            $parametro ='0';
            foreach($rdata as $row){
                $parametro= $parametro.','.$row->id_criterio;
            }

            
        }
        $parametro = $params->data;

        $sql="select 
            x.id_criterio, x.nombre, x.codigo, x.id_parent, x.id_afecta, x.estado, x.dc, x.orden, x.ver_hijo,
            (select count(*) from mat_criterio_semestre z where z.id_criterio=x.id_criterio and z.id_semestre_programa=".$params->id_semestre_programa.") as existe, '0' as opcion,
            (select count(*) from mat_criterio z where z.id_parent=x.id_criterio) as hijo
            from (
              select id_criterio, nombre, codigo, id_parent, id_afecta, estado, dc, orden,ver_hijo
                from eliseo.vw_mat_criterio
                where id_criterio in (".$parametro.")
                and ( id_parent is null )
                and estado='1'
                and id_criterio not in(select id_criterio from mat_criterio_semestre where id_semestre_programa=".$params->id_semestre_programa.")
              union
              select id_criterio, nombre, codigo, id_parent, id_afecta, estado, dc, orden ,ver_hijo
              from eliseo.vw_mat_criterio 
              where id_criterio in( select id_parent from mat_criterio
                where id_criterio in(".$parametro.")
                and id_criterio not in(select id_criterio from mat_criterio_semestre where id_semestre_programa=".$params->id_semestre_programa.")
                )
              and id_criterio not in(select id_criterio from mat_criterio_semestre where id_semestre_programa=".$params->id_semestre_programa.")
              and (tiene_hijo>0 or id_parent is null )
              and estado='1'
              union
              select id_criterio, nombre, codigo, id_parent, id_afecta, estado, dc, orden ,ver_hijo
              from eliseo.vw_mat_criterio 
              where id_afecta in (".$parametro.") 
              and (tiene_hijo=0 )
              and estado='1'
              and id_criterio not in(select id_criterio from mat_criterio_semestre where id_semestre_programa=".$params->id_semestre_programa.")
              union
              select id_criterio, nombre, codigo, id_parent, id_afecta, estado, dc, orden ,ver_hijo
              from eliseo.vw_mat_criterio 
              where id_afecta in (".$parametro.") 
              and (tiene_hijo>0 or id_parent is null )
              and estado='1'
              and id_criterio not in(select id_criterio from mat_criterio_semestre where id_semestre_programa=".$params->id_semestre_programa.")
              union
              select id_criterio, nombre, codigo, id_parent, id_afecta, estado, dc, orden,ver_hijo from eliseo.vw_mat_criterio where id_criterio in(
                select id_parent from mat_criterio
                where id_afecta in(".$parametro.")
                and id_criterio not in(select id_criterio from mat_criterio_semestre where id_semestre_programa=".$params->id_semestre_programa.")
              )
              and estado='1'
              order by dc desc,orden
            ) x 
            ";
        $data = DB::select($sql);
        $datos = array();
        foreach($data as $row){
            $items = array();
            $items['id_criterio']=$row->id_criterio;
            $items['nombre']=$row->nombre;
            $items['codigo']=$row->codigo;
            $items['id_parent']=$row->id_parent;
            $items['id_afecta']=$row->id_afecta;
            $items['estado']=$row->estado;
            $items['dc']=$row->dc;
            $items['orden']=$row->orden;
            $items['procedures']='';
            $items['tipo_proceso']='';
            $items['tipo_valor']='';
            $items['formula']='';
            $items['importe']='';
            $items['id_procedure']='';
            $items['existe']=$row->existe;
            $items['opcion']=$row->opcion;
            $items['hijo']=$row->hijo;
            $items['ver_hijo'] = $row->ver_hijo;
            if($row->dc=='D'){
                $query="select id_criterio, nombre, codigo, id_parent, id_afecta, estado, dc, orden,'' as procedures,'' as tipo_proceso ,'' as tipo_valor,'' as formula,'' as importe,'' as id_procedure,'0' as existe, '0' as opcion,'0' as hijo,ver_hijo
                      from eliseo.vw_mat_criterio 
                      where id_criterio in (".$parametro.") 
                      and (id_parent =".$row->id_criterio." )
                      and id_criterio not in(select id_criterio from mat_criterio_semestre where id_semestre_programa=".$params->id_semestre_programa.")
                      and estado='1'
                      order by dc desc,orden";

            }else{
                $query="select id_criterio, nombre, codigo, id_parent, id_afecta, estado, dc, orden,'' as procedures,'' as tipo_proceso ,'' as tipo_valor,'' as formula,'' as importe,'' as id_procedure ,'0' as existe,'0' as opcion,'0' as hijo,ver_hijo
                      from eliseo.vw_mat_criterio 
                      where id_afecta in (".$parametro.") 
                      and (id_parent =".$row->id_criterio." )
                      and id_afecta not in(select id_criterio from mat_criterio_semestre where id_semestre_programa=".$params->id_semestre_programa.")
                      and estado='1'
                      order by dc desc,orden
                    ";
            }
            $datachild = DB::select($query);

            
            $items['childs']=$datachild;
            
            
            $datos[]=$items;

        }
        return $datos;

    }


    public static function index($id_nivel_ensenanza, $nombre){
        return self::criterion($id_nivel_ensenanza, $nombre);
    }


    public static function add($data) {
        $create_criterio_id = DB::transaction(function() use($data) {
            Criterion::insert($data);
            return DB::getSequence()->currentValue('SQ_MAT_CRITERIO_ID');
        });
        return Criterion::find($create_criterio_id);
    }
    public static function show($id_criterio) {
        return Criterion::find($id_criterio);
    }


    public static function update($data, $id) {
        Criterion::where('id_criterio', $id)->update($data);
        return Criterion::findOrFail($id);
    }


    public static function criterionListOption($params) {
        return self::criterionList($params);
    }
    public static function criterionListPend($params) {
        $query="select id_criterio, nombre, codigo, id_parent, id_afecta, estado, dc, orden,ver_hijo 
              from eliseo.vw_mat_criterio 
              where id_criterio   in(select id_criterio from mat_criterio_semestre where id_semestre_programa=".$params->id_semestre_programa.")
              and dc='D'
              and id_criterio in(
                select id_afecta from vw_mat_criterio where id_afecta in(
                  select id_criterio from mat_criterio_semestre where id_semestre_programa=".$params->id_semestre_programa."
                ) and id_afecta is not null
              )
              and estado='1'
              order by orden
            ";
        $data = DB::select($query);
        return $data;
    }
    public static function criterionLinenalHerarchy($params) {
        return self::criterionLineal($params);
    }

    public static function getListCriteriaSemester($idSemester) {
//        dd('asdfds', $idSemester);
        return Db::Table('MAT_CRITERIO_SEMESTRE')
            ->join('MAT_CRITERIO', 'MAT_CRITERIO_SEMESTRE.ID_CRITERIO', '=', 'MAT_CRITERIO.ID_CRITERIO')
//            ->where('ID_SEMESTRE', $idSemester)
            ->get();
    }

    public static function getListContractMode() {
//        dd('asdfds', $idSemester);
        return Db::Table('DAVID.MODO_CONTRATO')
            ->select('ID_MODO_CONTRATO','NOMBRE','CODIGO')
            ->get();
    }
    public static  function getTypeDiscount($estado=''){
        
        $q = DB::table('fin_tipo_dscto');
        if(strlen($estado)>0){
            $q->where('estado',$estado);
        }
        
        $q->select('tipo_dscto','nombre','consemestre','estado');
                
        $data = $q->get();

        return $data;
    }
    public static function semester(){
        return DB::table('DAVID.ACAD_SEMESTRE')
            ->select('id_semestre','semestre','nombre','estado')
            ->orderBy('semestre', 'desc')
            ->paginate(1000);
    }


}