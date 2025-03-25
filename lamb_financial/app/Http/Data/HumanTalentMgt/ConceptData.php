<?php
namespace App\Http\Data\HumanTalentMgt;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\HumanTalentMgt\ComunData;

class ConceptData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    //concepto sinat
    public static function ListSunatConcept($id_tipo_concepto,$id_tipo_concepto_sunat,$nombre,$perpage){
        $q =  DB::table('plla_concepto_planilla_sunat as a');
        $q->join('plla_tipo_concepto_sunat as b','b.id_tipo_concepto_sunat','=','a.id_tipo_concepto_sunat');
        $q->join('plla_tipo_concepto as c','c.id_tipo_concepto','=','b.id_tipo_concepto');
        if(strlen($id_tipo_concepto)>0){
            $q->where('b.id_tipo_concepto',$id_tipo_concepto);
        }
        if(strlen($id_tipo_concepto_sunat)>0){
            $q->where('a.id_tipo_concepto_sunat',$id_tipo_concepto_sunat);
        }
        $q->whereraw("(".ComunData::fnBuscar('a.nombre').' like '.ComunData::fnBuscar("'%".$nombre."%'")." or a.codigo like '%".$nombre."%' or ".ComunData::fnBuscar("a.codigo|| '-' ||a.nombre")."  like ".ComunData::fnBuscar("'%".$nombre."%'").")");
        $q->select('a.id_concepto_planilla_sunat',
                'a.id_tipo_concepto_sunat',
                'b.id_tipo_concepto',
                'a.codigo',
                'a.nombre',
                'b.nombre as tipo_concepto_sunat',
                'c.nombrecorto',
                'a.vigencia');
        $q->orderBy('b.orden','asc');
        $q->orderBy('a.nombre','asc');

 
        $data=$q->paginate($perpage);
        
        return $data;
    }
    public static  function ShowSunatConcept($id_concepto_planilla_sunat){
        $objeto = DB::table('plla_concepto_planilla_sunat as a')
                ->join('plla_tipo_concepto_sunat as b','b.id_tipo_concepto_sunat','=','a.id_tipo_concepto_sunat')
                ->where('a.id_concepto_planilla_sunat',$id_concepto_planilla_sunat)
                ->select('a.id_concepto_planilla_sunat',
                'a.id_tipo_concepto_sunat',
                'b.id_tipo_concepto',
                'a.codigo',
                'a.nombre',
                'a.vigencia')
                ->first();
        return $objeto;
    }
    public static function AddSunatConcept($request){
        $id_concepto_planilla_sunat = ComunData::correlativo('plla_concepto_planilla_sunat', 'id_concepto_planilla_sunat');
        if($id_concepto_planilla_sunat>0){
            
 
            $count = DB::table('plla_concepto_planilla_sunat')->whereraw("codigo = '".$request->codigo."'")->count();
            if($count==0){
                $save = DB::table('plla_concepto_planilla_sunat')->insert(
                        [
                        'id_concepto_planilla_sunat' =>  $id_concepto_planilla_sunat,
                        'id_tipo_concepto_sunat' =>  $request->id_tipo_concepto_sunat,
                        'codigo' => $request->codigo,
                        'nombre' => $request->nombre,
                        'vigencia'=>1
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
                        'message'=>$request->codigo.' ya existe',
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
    public static function UpdateSunatConcept($id_concepto_planilla_sunat,$request){

        $scalegroup= DB::table('plla_concepto_planilla_sunat')->where('id_concepto_planilla_sunat',$id_concepto_planilla_sunat);
        if(!empty($scalegroup)){
            $count = DB::table('plla_concepto_planilla_sunat')->whereraw("codigo = '".$request->codigo."'")->where('id_concepto_planilla_sunat','<>',$id_concepto_planilla_sunat)->count();
            
            if($count==0){
                $result = DB::table('plla_concepto_planilla_sunat')
                    ->where('id_concepto_planilla_sunat', $id_concepto_planilla_sunat)
                    ->update(['id_tipo_concepto_sunat' =>  $request->id_tipo_concepto_sunat,
                        'codigo' => $request->codigo,
                        'nombre' => $request->nombre,
                        'vigencia'=>$request->vigencia
                        ]); 
 
                if($result>0){
                    $response=[
                        'success'=> true,
                        'message'=>'',
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
                    'message'=>$request->codigo.' ya existe registrado',
                ];
            }
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se ha encontrado concepto sunat',
            ];
        }
        return $response;
    }
    public static function DeleteSunatConcept($id_concepto_planilla_sunat){
        $count = DB::table('plla_concepto_planilla')->where('id_concepto_planilla_sunat',$id_concepto_planilla_sunat)->count();
        if($count==0){
            
            $rows = DB::table('plla_concepto_planilla_sunat')->where('id_concepto_planilla_sunat', $id_concepto_planilla_sunat)->delete();
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
                'message'=>'Esta asociado a planilla concepto                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       ',
            ];
        }
        return $response;
    }
    //concepto aps
    public static function ListApsConcept($id_tipo_concepto,$id_tipoconceptoaps,$nombre,$perpage){
        $q =  DB::table('aps_concepto_planilla as a');
        $q->join('tipo_concepto_planilla as b','b.id_tipoconceptoaps','=','a.id_tipoconceptoaps');
        if(strlen($id_tipo_concepto)>0){
            $q->join('plla_tipo_concepto as c','c.id_tipo_concepto','=','a.tipo');
            $q->where('c.id_tipo_concepto',$id_tipo_concepto);
        }else{
            $q->leftjoin('plla_tipo_concepto as c','c.id_tipo_concepto','=','a.tipo');
        }
        if(strlen($id_tipoconceptoaps)>0){
            $q->where('b.id_tipoconceptoaps',$id_tipoconceptoaps);
        }
        
        $q->whereraw("(".ComunData::fnBuscar('a.nombre').' like '.ComunData::fnBuscar("'%".$nombre."%'")." or a.id_conceptoaps like '%".$nombre."%' or ".ComunData::fnBuscar("a.id_conceptoaps|| '-' ||a.nombre")."  like ".ComunData::fnBuscar("'%".$nombre."%'").")");
        $q->select('a.id_conceptoaps',
                'a.id_tipoconceptoaps',
                'c.id_tipo_concepto',
                'a.nombre',
                'b.nombre as tipo_concepto_planilla',
                'c.nombre as tipo');
        $q->orderBy('c.orden','asc');
        $q->orderBy('a.nombre','asc');

 
        $data=$q->paginate($perpage);
        
        return $data;
    }
    // concepto
    public static function ListConcept($id_tipo_concepto,$nombre,$perpage,$paginar=true){
        $q =  DB::table('plla_concepto_planilla as a');
        $q->join('plla_tipo_concepto as b','b.id_tipo_concepto','=','a.id_tipo_concepto');
        $q->leftjoin('aps_concepto_planilla as c','c.id_conceptoaps','=','a.id_conceptoaps');
        $q->leftjoin('plla_concepto_planilla_sunat as d','d.id_concepto_planilla_sunat','=','a.id_concepto_planilla_sunat');
        if(strlen($id_tipo_concepto)>0){
            $q->where('a.id_tipo_concepto',$id_tipo_concepto);
        }
        $q->whereraw("(".ComunData::fnBuscar('a.nombre').' like '.ComunData::fnBuscar("'%".$nombre."%'")." or
                    ".ComunData::fnBuscar('b.nombre').' like '.ComunData::fnBuscar("'%".$nombre."%'")." or 
                    ".ComunData::fnBuscar('c.nombre').' like '.ComunData::fnBuscar("'%".$nombre."%'")." or
                     a.codigo like '%".$nombre."%' or d.codigo like '%".$nombre."%' or  c.id_conceptoaps like '%".$nombre."%')");
        $q->select('a.id_concepto_planilla',
                'a.id_concepto_planilla_sunat',
                'a.id_conceptoaps',
                'a.id_tipo_concepto',
                'a.nombre',
                'a.codigo',
                'a.tipo_formula',
                'a.formula',
                'a.id_parent',
                'a.id_descuento',
                'a.emp_essalud',
                'a.emp_essaludsctr',
                'a.tra_snp',
                'a.tra_spp',
                'a.tra_retntaqcat',
                'a.pen_essalud',
                'a.gratificacion',
                'a.cts',
                'a.vacaciones',
                'a.tipo',
                'a.tipo_ejecucion',
                'a.orden',
                'a.vigencia',
                'a.nodiezmo',
                'a.nodescuentojud',
                'b.nombre as tipo_concepto_planilla',
                'c.nombre as aps',
                'd.nombre as sunat',
                'd.codigo as codsunat',
                DB::raw("(select x.id_concepto_planilla||'-'||x.nombre from  plla_concepto_planilla x where x.id_concepto_planilla=a.id_descuento) as descuento,
                    (select count(*) from plla_concepto_planilla_proc x where x.id_concepto_planilla=a.id_concepto_planilla) as proc")
               );
        $q->orderBy('b.orden','asc');
        $q->orderBy('a.orden','asc');
        if ($paginar){
            $data=$q->paginate($perpage);
        }else{
            $data=$q->get();
        }
        
        return $data;
    }
    public static function ListConceptByType($id_tipo_concepto,$id_concepto_planilla){
        $q =  DB::table('plla_concepto_planilla as a');
        $q->where('a.id_tipo_concepto',$id_tipo_concepto);
        $q->where('a.id_concepto_planilla','<>',$id_concepto_planilla);
        $q->select('a.id_concepto_planilla',
                'a.id_concepto_planilla_sunat',
                'a.id_conceptoaps',
                'a.id_tipo_concepto',
                'a.nombre',
                'a.codigo',
                'a.tipo_formula',
                'a.formula',
                'a.id_parent',
                'a.id_descuento',
                'a.emp_essalud',
                'a.emp_essaludsctr',
                'a.tra_snp',
                'a.tra_spp',
                'a.tra_retntaqcat',
                'a.pen_essalud',
                'a.gratificacion',
                'a.cts',
                'a.vacaciones',
                'a.tipo',
                'a.tipo_ejecucion',
                'a.orden',
                'a.vigencia',
                'a.nodiezmo',
                'a.nodescuentojud'
               );
        $q->orderBy('a.nombre','asc');
        $data=$q->get();
        
        return $data;
    }
    public static  function ShowConcept($id_concepto_planilla){
        $q =  DB::table('plla_concepto_planilla as a');
        $q->join('plla_tipo_concepto as b','b.id_tipo_concepto','=','a.id_tipo_concepto');
        $q->leftjoin('aps_concepto_planilla as c','c.id_conceptoaps','=','a.id_conceptoaps');
        $q->leftjoin('plla_concepto_planilla_sunat as d','d.id_concepto_planilla_sunat','=','a.id_concepto_planilla_sunat');
        $q->where('a.id_concepto_planilla',$id_concepto_planilla);
        $q->select('a.id_concepto_planilla',
                'a.id_concepto_planilla_sunat',
                'a.id_conceptoaps',
                'a.id_tipo_concepto',
                'a.nombre',
                'a.codigo',
                'a.tipo_formula',
                'a.formula',
                'a.id_parent',
                'a.id_descuento',
                'a.emp_essalud',
                'a.emp_essaludsctr',
                'a.tra_snp',
                'a.tra_spp',
                'a.tra_retntaqcat',
                'a.pen_essalud',
                'a.gratificacion',
                'a.cts',
                'a.vacaciones',
                'a.tipo',
                'a.tipo_ejecucion',
                'a.orden',
                'a.vigencia',
                'a.nodiezmo',
                'a.nodescuentojud',
                'b.nombre as tipo_concepto_planilla',
                'c.nombre as aps',
                'd.nombre as sunat',
                'd.codigo as codsunat'
               );
        $objeto= $q->first();
        return $objeto;
    }
    public static function listConceptProc($id_concepto_planilla){
       
        $data = DB::table('plla_concepto_planilla_proc')
            ->where('id_concepto_planilla',$id_concepto_planilla)
            ->select('id_concepto_planilla_proc',
                'id_concepto_planilla',
                'proceso',
                'descripcion',
                "vigencia as vigenciareal",
                DB::raw("'' as vigencia,
                'U' as opc,
                'S' as edi,
                'S' as del,
                'N' as sav,
                'N' as can,
                '0' as id_temp,
                'U' as opctmp")
            )
            ->orderBy('id_concepto_planilla_proc','asc')
            ->get();
        return $data;
    }
    public static function AddConcept($request){
        $id_concepto_planilla = ComunData::correlativo('plla_concepto_planilla', 'id_concepto_planilla');
        if($id_concepto_planilla>0){
            
 
            $count = DB::table('plla_concepto_planilla')->whereraw("codigo = '".$request->codigo."'")->count();
            if($count==0){
                $save = DB::table('plla_concepto_planilla')->insert(
                        ['id_concepto_planilla'=>$id_concepto_planilla,
                        'id_concepto_planilla_sunat'=>$request->id_concepto_planilla_sunat,
                        'id_conceptoaps'=>$request->id_conceptoaps,
                        'id_tipo_concepto'=>$request->id_tipo_concepto,
                        'nombre'=>$request->nombre,
                        'codigo'=>$request->codigo,
                        'tipo_formula'=>$request->tipo_formula,
                        'formula'=>$request->formula,
                        'id_parent'=>$request->id_parent,
                        'id_descuento'=>$request->id_descuento,
                        'emp_essalud'=>$request->emp_essalud,
                        'tra_snp'=>$request->tra_snp,
                        'tra_spp'=>$request->tra_spp,
                        'tra_retntaqcat'=>$request->tra_retntaqcat,
                        'gratificacion'=>$request->gratificacion,
                        'cts'=>$request->cts,
                        'vacaciones'=>$request->vacaciones,
                        'tipo'=>$request->tipo,
                        'orden'=>$request->orden,
                        'nodiezmo'=>$request->nodiezmo,
                        'nodescuentojud'=>$request->nodescuentojud,
                        'vigencia'=>1
                        ]
                    );
                if($save){

                    $rspta= ConceptData::detailProc($request,$id_concepto_planilla);
                    if($rspta!=0){
                        $response=[
                        'success'=> false,
                        'message'=>'Error en la actualización de proceso',
                        ];
                    }else{
                       $response=[
                        'success'=> true,
                        'message'=>'',
                        ]; 
                    }

                    
                }else{
                    $response=[
                        'success'=> false,
                        'message'=>'No se puede insertar',
                    ];
                }
            }else{
                $response=[
                        'success'=> false,
                        'message'=>$request->codigo.' ya existe',
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
    private static function detailProc($request,$id_concepto_planilla){
        $error = 0;
        foreach ($request->detail as $row) {
            $reg = (object)$row;
            if($reg->opc=='I'){
                $id_concepto_planilla_proc = ComunData::correlativo('plla_concepto_planilla_proc', 'id_concepto_planilla_proc');
                if($id_concepto_planilla_proc>0){
                    $save = DB::table('plla_concepto_planilla_proc')->insert(
                        ['id_concepto_planilla_proc'=>$id_concepto_planilla_proc,
                        'id_concepto_planilla'=>$id_concepto_planilla,
                        'proceso'=>$reg->proceso,
                        'descripcion'=>$reg->descripcion,
                        'vigencia'=>$reg->vigenciareal
                        ]
                    );
                    if(!$save){
                        $error++; 
                    }
                }else{
                   $error++; 
                }
            }else{
                $result = DB::table('plla_concepto_planilla_proc')
                    ->where('id_concepto_planilla_proc', $reg->id_concepto_planilla_proc)
                    ->update([
                        'proceso'=>$reg->proceso,
                        'descripcion'=>$reg->descripcion,
                        'vigencia'=>$reg->vigenciareal
                        ]); 
 
                if($result<>1){
                    $error++;
                }
            }
            
        }

        
        return $error;

    }
    public static function UpdateConcept($id_concepto_planilla,$request){

        $ROWS= DB::table('plla_concepto_planilla')->where('id_concepto_planilla',$id_concepto_planilla);
        if(!empty($ROWS)){
            $count = DB::table('plla_concepto_planilla')->whereraw("codigo = '".$request->codigo."'")->where('id_concepto_planilla','<>',$id_concepto_planilla)->count();
            
            if($count==0){
                $result = DB::table('plla_concepto_planilla')
                    ->where('id_concepto_planilla', $id_concepto_planilla)
                    ->update([
                        'id_concepto_planilla_sunat'=>$request->id_concepto_planilla_sunat,
                        'id_conceptoaps'=>$request->id_conceptoaps,
                        'id_tipo_concepto'=>$request->id_tipo_concepto,
                        'nombre'=>$request->nombre,
                        'codigo'=>$request->codigo,
                        'tipo_formula'=>$request->tipo_formula,
                        'formula'=>$request->formula,
                        'id_parent'=>$request->id_parent,
                        'id_descuento'=>$request->id_descuento,
                        'emp_essalud'=>$request->emp_essalud,
                        'tra_snp'=>$request->tra_snp,
                        'tra_spp'=>$request->tra_spp,
                        'tra_retntaqcat'=>$request->tra_retntaqcat,
                        'gratificacion'=>$request->gratificacion,
                        'cts'=>$request->cts,
                        'vacaciones'=>$request->vacaciones,
                        'tipo'=>$request->tipo,
                        'orden'=>$request->orden,
                        'vigencia'=>$request->vigencia,
                        'nodiezmo'=>$request->nodiezmo,
                        'nodescuentojud'=>$request->nodescuentojud
                        ]); 
 
                if($result>0){
                    $rspta= ConceptData::detailProc($request,$id_concepto_planilla);
                    if($rspta!=0){
                        $response=[
                        'success'=> false,
                        'message'=>'Error en la actualización de proceso',
                        ];
                    }else{
                       $response=[
                        'success'=> true,
                        'message'=>'',
                        ]; 
                    }

                }else{
                    $response=[
                        'success'=> false,
                        'message'=>'No se puede modificar',
                    ];
                }
            }else{
                $response=[
                    'success'=> false,
                    'message'=>$request->codigo.' ya existe registrado',
                ];
            }
        }else{
            $response=[
                'success'=> false,
                'message'=>'No se ha encontrado concepto sunat',
            ];
        }
        return $response;
    }
    public static function DeleteConceptProc($id_concepto_planilla_proc){
        $count = DB::table('plla_planilla_entidad_concepto')->where('id_concepto_planilla_proc',$id_concepto_planilla_proc)->count();
        if($count==0){
            
            $rows = DB::table('plla_concepto_planilla_proc')->where('id_concepto_planilla_proc', $id_concepto_planilla_proc)->delete();
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
                'message'=>'Esta asociado a planilla entidad concepto                                                                                                                               ',
            ];
        }
        return $response;
    }
    public static function DeleteConcept($id_concepto_planilla){
        $count = DB::table('plla_planilla_detalle')->where('id_concepto_planilla',$id_concepto_planilla)->count();
        if($count==0){
            
            $rows = DB::table('plla_concepto_planilla')->where('id_concepto_planilla', $id_concepto_planilla)->delete();
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
                'message'=>'Esta asociado a detalle conecpto pplanilla                                                                                                                               ',
            ];
        }
        return $response;
    }
    //asignacion de conceptos a grupo y tipo planilla
    public static function ListConceptPayrollGroup($id_tipo_concepto,$id_planilla_entidad,$perpage){
        $q =  DB::table('plla_planilla_entidad_concepto as w');
        $q->join('plla_concepto_planilla as a','a.id_concepto_planilla','=','w.id_concepto_planilla');
        $q->join('plla_tipo_concepto as b','b.id_tipo_concepto','=','a.id_tipo_concepto');
        $q->leftjoin('aps_concepto_planilla as c','c.id_conceptoaps','=','a.id_conceptoaps');
        $q->leftjoin('plla_concepto_planilla_sunat as d','d.id_concepto_planilla_sunat','=','a.id_concepto_planilla_sunat');
        $q->leftjoin('plla_concepto_planilla_proc as p','p.id_concepto_planilla_proc','=','w.id_concepto_planilla_proc');
        $q->where('w.id_planilla_entidad',$id_planilla_entidad);
        if(strlen($id_tipo_concepto)>0){
            $q->where('a.id_tipo_concepto',$id_tipo_concepto);
        }
        $q->select('a.id_concepto_planilla',
                'w.id_planilla_entidad',
                'a.id_concepto_planilla_sunat',
                'a.id_conceptoaps',
                'a.id_tipo_concepto',
                'a.nombre',
                'a.codigo',
                'a.tipo_formula',
                'a.formula',
                'a.id_parent',
                'a.id_descuento',
                'a.emp_essalud',
                'a.emp_essaludsctr',
                'a.tra_snp',
                'a.tra_spp',
                'a.tra_retntaqcat',
                'a.pen_essalud',
                'a.gratificacion',
                'a.cts',
                'a.vacaciones',
                'a.tipo',
                'a.tipo_ejecucion',
                'a.orden',
                'a.vigencia',
                'b.nombrecorto as tipo_concepto_planilla',
                'c.nombre as aps',
                'd.nombre as sunat',
                'd.codigo as codsunat',
                'p.proceso',
                'p.id_concepto_planilla_proc',
                DB::raw("(select count(*) from plla_concepto_planilla_proc x where x.id_concepto_planilla=a.id_concepto_planilla) as proc")
               );
        $q->orderBy('b.orden','asc');
        $q->orderBy('a.orden','asc');
 
        $data=$q->paginate($perpage);
        
        return $data;
 
    }
    public static function ListConceptPayrollGroupAssign($id_tipo_concepto,$id_planilla_entidad){
        $q =  DB::table('plla_concepto_planilla as a');
        $q->leftjoin('aps_concepto_planilla as c','c.id_conceptoaps','=','a.id_conceptoaps');
        $q->leftjoin('plla_concepto_planilla_sunat as d','d.id_concepto_planilla_sunat','=','a.id_concepto_planilla_sunat');
        $q->where('a.id_tipo_concepto',$id_tipo_concepto);
        $q->whereraw("a.id_concepto_planilla not in(
                select id_concepto_planilla from plla_planilla_entidad_concepto where id_planilla_entidad=".$id_planilla_entidad." 
                )");
        $q->select('a.id_concepto_planilla',
                'a.id_concepto_planilla_sunat',
                'a.id_conceptoaps',
                'a.id_tipo_concepto',
                'a.nombre',
                'a.codigo',
                'c.nombre as aps',
                'd.nombre as sunat',
                'd.codigo as codsunat',
                DB::raw("'' as opcion, '0' as opcionreal")
               );
        //$q->orderBy('b.orden','asc');
        $q->orderBy('a.nombre','asc');
 
        $data=$q->get();
        $array=[];
        foreach($data as $row){
            $item=array();
            $item['id_concepto_planilla']=$row->id_concepto_planilla;
            $item['id_concepto_planilla_sunat']=$row->id_concepto_planilla_sunat;
            $item['id_conceptoaps']=$row->id_conceptoaps;
            $item['nombre']=$row->nombre;
            $item['codigo']=$row->codigo;
            $item['aps']=$row->aps;
            $item['sunat']=$row->sunat;
            $item['codsunat']=$row->codsunat;
            $item['opcion']='';
            $item['opcionreal']='0';
            $item['id_concepto_planilla_proc']='';
            $datproc=DB::table('plla_concepto_planilla_proc')
                        ->where('id_concepto_planilla',$row->id_concepto_planilla)
                        ->where('vigencia',1)
                        ->orderBy('proceso')
                        ->get();
            $item['detproceso']= $datproc;
            $array[]=$item;
        }
        
        return $array;
    }
    /*public static function ListConceptPayrollGroupAssign($id_tipo_concepto,$id_planilla_entidad){
        $q =  DB::table('plla_concepto_planilla as a');
        $q->join('plla_tipo_concepto as b','b.id_tipo_concepto','=','a.id_tipo_concepto');
        $q->leftjoin('aps_concepto_planilla as c','c.id_conceptoaps','=','a.id_conceptoaps');
        $q->leftjoin('plla_concepto_planilla_sunat as d','d.id_concepto_planilla_sunat','=','a.id_concepto_planilla_sunat');
        $q->where('a.id_tipo_concepto',$id_tipo_concepto);
        $q->whereraw("a.id_concepto_planilla not in(
                select id_concepto_planilla from plla_planilla_entidad_concepto where id_planilla_entidad=".$id_planilla_entidad." 
                )");
        $q->select('a.id_concepto_planilla',
                'a.id_concepto_planilla_sunat',
                'a.id_conceptoaps',
                'a.id_tipo_concepto',
                'a.nombre',
                'a.codigo',
                'a.tipo_formula',
                'a.formula',
                'a.id_parent',
                'a.id_descuento',
                'a.emp_essalud',
                'a.emp_essaludsctr',
                'a.tra_snp',
                'a.tra_spp',
                'a.tra_retntaqcat',
                'a.pen_essalud',
                'a.gratificacion',
                'a.cts',
                'a.vacaciones',
                'a.tipo',
                'a.tipo_ejecucion',
                'a.orden',
                'a.vigencia',
                'b.nombre as tipo_concepto_planilla',
                'c.nombre as aps',
                'd.nombre as sunat',
                'd.codigo as codsunat',
                DB::raw("'' as opcion, '0' as opcionreal")
               );
        $q->orderBy('b.orden','asc');
        $q->orderBy('a.nombre','asc');
 
        $data=$q->get();
        
        return $data;
    }*/
    public static function showConceptPayrollGroup($id_concepto_planilla,$id_planilla_entidad){
        $object = DB::table('plla_planilla_entidad_concepto')
            ->where('id_concepto_planilla', $id_concepto_planilla)
            ->where('id_planilla_entidad', $id_planilla_entidad)
            ->first(); 
        return $object;
    }
    public static function AddConceptPayrollGroup($request){
        $id_planilla_entidad = $request->id_planilla_entidad;
        $error = 0;
        $j=0;
        foreach ($request->details as $dato) {
            $item =(object) $dato;
            $count = DB::table('plla_planilla_entidad_concepto')
                    ->where('id_planilla_entidad',$id_planilla_entidad)
                    ->where('id_concepto_planilla',$item->id_concepto_planilla)
                    ->count();
            if($count==0){
                $save = DB::table('plla_planilla_entidad_concepto')->insert(
                        ['id_concepto_planilla'=>$item->id_concepto_planilla,
                        'id_planilla_entidad'=>$id_planilla_entidad,
                        'id_concepto_planilla_proc'=>$item->id_concepto_planilla_proc,
                        'vigencia'=>1
                        ]
                    );
                if(!$save){
                    $error++;
                }else{
                    $j++;
                }
            }else{
                $error++;
            }
        }
        
        if($j==0){
            $response=[
                'success'=> false,
                'message'=>'No hay dato para registro',
            ];
        }else{
            if($error==0){
                $response=[
                    'success'=> true,
                    'message'=>'Se ha registrado correctamente',
                ];
            }else{
                $response=[
                    'success'=> false,
                    'message'=>'No se ha registrado',
                ];
            }
            
        }
        return $response;
    }
    public static function UpdateConceptPayrollGroup($id_concepto_planilla,$id_planilla_entidad,$request){

        
        $result = DB::table('plla_planilla_entidad_concepto')
            ->where('id_concepto_planilla', $id_concepto_planilla)
            ->where('id_planilla_entidad', $id_planilla_entidad)
            ->update([
                'id_concepto_planilla_proc'=>$request->id_concepto_planilla_proc,
                'vigencia'=>$request->vigencia
                ]); 

        if($result>0){
            
               $response=[
                'success'=> true,
                'message'=>'',
                ]; 
            

        }else{
            $response=[
                'success'=> false,
                'message'=>'No se puede modificar',
            ];
        }
        
        return $response;
    }
    public static function DeleteConceptPayrollGroup($id_planilla_entidad,$id_concepto_planilla){
        
            
        $rows = DB::table('plla_planilla_entidad_concepto')
                ->where('id_planilla_entidad', $id_planilla_entidad)
                ->where('id_concepto_planilla', $id_concepto_planilla)
                ->delete();
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

        
        return $response;
    }
}
?>

