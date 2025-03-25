<?php
namespace App\Http\Data\HumanTalent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\APSData;
use Carbon\Carbon;
use DateTime;
class ReporteData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function buscarPersona($request){   
        $dato = $request->query('dato');
        $id_entidad = $request->query('id_entidad');
        $id_anho = $request->query('id_anho');
        $id_mes = $request->query('id_mes');
        $all = $request->query('all');
        $datos=$dato; 
        $query=DB::table("moises.vw_persona_natural_light as p");
        $query->select("p.id_persona",
                        "p.nombre",
                        "p.paterno",
                        "p.materno",
                        "p.nom_persona"
                        //,
                        // "p.num_documento"
                    );
        $datos=$datos."%";
        if($id_entidad){
            $query->join('aps_empleado as e','e.id_persona', '=', DB::raw('p.id_persona and e.id_entidad='.$id_entidad));
        }
        $query->whereRaw("(lower(p.paterno) like lower('".$datos."') or lower(p.materno) like lower('".$datos."') or lower(p.nombre) like lower('".$datos."')
        or lower(p.nombre||' '||p.paterno) like lower('".$datos."') or lower(p.nombre||' '||p.paterno||' '||p.materno) like lower('".$datos."')
        or lower(p.paterno||' '||p.materno) like lower('".$datos."') or lower(p.paterno||' '||p.materno||' '||p.nombre) like lower('".$datos."') or num_documento like '".$dato."')");
            /*$query->whereRaw("FC_BUSCAR_TEXTO(lower(paterno)) like lower('".$datos."') or FC_BUSCAR_TEXTO(lower(materno)) like lower('".$datos."') or FC_BUSCAR_TEXTO(lower(nombre)) like lower('".$datos."')
or FC_BUSCAR_TEXTO(lower(nombre||' '||paterno)) like lower('".$datos."') or FC_BUSCAR_TEXTO(lower(nombre||' '||paterno||' '||materno)) like lower('".$datos."')
or FC_BUSCAR_TEXTO(lower(paterno||' '||materno)) like lower('".$datos."') or FC_BUSCAR_TEXTO(lower(paterno||' '||materno||' '||nombre)) like lower('".$datos."') or num_documento like '".$dato."'");*/
        
        if($id_entidad and $id_anho and $id_mes){
            $query->whereRaw("to_number(".$id_anho."||LPAD(".$id_mes.",2,'0'))>=to_number(to_char(e.fec_inicio, 'YYYYMM')) and (to_number(".$id_anho."||LPAD(".$id_mes.",2,'0'))<=to_number(to_char(e.fec_termino, 'YYYYMM')) or e.fec_termino is null)");
        }else {
            if($id_entidad and $id_anho){
                $query->whereRaw("to_number($id_anho)>=to_number(to_char(e.fec_inicio, 'YYYY')) and to_number($id_anho)<=to_number(to_char(e.fec_termino, 'YYYY'))");
            }
        }
        $query->orderBy('p.paterno', 'asc');
        $query->orderBy('p.materno', 'asc');
        $query->orderBy('p.nombre', 'asc');
        if(!$all){
            $query->take(10);
        }
        $query->distinct();
        $data = $query->get(); 
        return $data;
    }
    public static function fichapersona($id_persona){
        $objdatgen = DB::table('moises.vw_persona_natural_light as a')
                    ->where('a.id_persona','=',$id_persona)
                    ->leftjoin('moises.persona_direccion as b','a.id_persona',DB::raw('b.id_persona and b.id_tipodireccion=4 and b.es_activo=1'))
                    ->leftjoin('moises.persona_telefono as c','a.id_persona',DB::raw('c.id_persona and c.id_tipotelefono in (5,9)'))
                    ->join('moises.persona_natural as d','d.id_persona','=','a.id_persona')
                    ->leftjoin('jose.school_departamento as e','e.dep_id','=','d.id_departamento')
                    ->leftjoin('jose.school_provincia as f','f.pro_id','=','d.id_provincia')
                    ->leftjoin('jose.school_distrito as g','g.dis_id','=','d.id_distrito')
                    ->select(
                            'a.id_persona',
                            'a.paterno',
                            'a.materno',
                            'a.nombre',
                            'a.nom_persona',
                            "a.tipo_estado_civil as estado_civil",
                            "a.tipo_tratamiento",
                            "e.dep_nombre as departamento",
                            "f.pro_nombre as provincia",
                            "g.dis_nombre as distrito",
                            'b.direccion',
                            'c.num_telefono as telefono',
                            DB::raw("decode(a.sexo, '1', 'Masculino','Femenino') as sexo,
                            to_char(a.fec_nacimiento,'DD/MM/YYYY') as fec_nacimiento
                            ,(e.dep_nombre||case when f.pro_nombre is null then '' else '-'||f.pro_nombre end||case when g.dis_nombre is null then '' else '-'||g.dis_nombre end) as lugar_nacimiento")
                    )
                    ->OrderBy('b.id_direccion','desc')
                    ->first();
        
        $objfecmis = DB::table('eliseo.vw_aps_empleado')
                    ->where('id_persona',$id_persona)
                    ->select(
                            DB::raw("to_char(fec_misionero,'DD/MM/YYYY') as fec_misionero")
                       )
                       ->distinct()
                       ->first();
        
        $objdoc = DB::table('eliseo.vw_aps_empleado as a')
                    ->join('moises.tipo_documento as b','b.id_tipodocumento','=','a.id_tipodocumento')
                    ->where('a.id_persona',$id_persona)
                    ->select(
                            'b.siglas as tipo_documento',
                            'num_documento',
                            'num_snp',
                            'num_cuspp'
                       )
                       ->distinct()
                       ->first();
        
        $datemail = DB::table('moises.persona_virtual as a')
                    ->join('moises.tipo_virtual as b','b.id_tipovirtual','=','a.id_tipovirtual')
                    ->where('a.id_persona',$id_persona)
                    ->select(
                            'b.siglas as tipo_direccion',
                            'a.direccion'
                       )
                       ->OrderBy('a.id_virtual','asc')
                       ->get();
        $email = DB::table('moises.persona_virtual as a')
                    ->join('moises.tipo_virtual as b','b.id_tipovirtual','=','a.id_tipovirtual')
                    ->where('a.id_persona',$id_persona)
                    ->select(
                            'b.siglas as tipo_direccion',
                            'a.direccion'
                       )
                       ->OrderBy('a.id_virtual','desc')
                       ->first();

        $datreligion = DB::table('jose.school_persona_religion as a')
                       ->join('jose.school_religion as b','b.id','=','a.id_religion')
                       ->where('a.id_persona',$id_persona)
                       ->select(
                               'b.religion_nombre as religion'
                          )
                          ->OrderBy('a.id_religion','desc')
                          ->first();
        
        
        $msg="'Hasta la actualidad'";
        $format="'DD/MM/YYYY'";
        $qry = 'SELECT DISTINCT * FROM(
            SELECT
                "ELISEO"."A"."ID_ENTIDAD",
                "ELISEO"."B"."NOMBRE",
                "ELISEO"."C"."NOMBRE" AS "DEPTO",
                TO_CHAR (fec_inicio, '.$format.') AS fec_inicio,
                COALESCE (
                    TO_CHAR (fec_termino, '.$format.'),'.$msg.'
                ) AS fec_termino
            FROM
                "ELISEO"."VW_APS_EMPLEADO" A
            INNER JOIN "ELISEO"."CONTA_ENTIDAD" b ON "ELISEO"."B"."ID_ENTIDAD" = "ELISEO"."A"."ID_ENTIDAD"
            LEFT JOIN "ELISEO"."CONTA_ENTIDAD_DEPTO" c ON "ELISEO"."C"."ID_ENTIDAD" = A .id_entidad
                AND c.id_depto = A .id_depto
            WHERE
                "ELISEO"."A"."ID_PERSONA" = '.$id_persona.'
            ORDER BY
                "ELISEO"."A"."FEC_INICIO" DESC
                )
            ORDER BY
                FEC_INICIO DESC';
        $datcontrato = DB::connection('oracle')->select($qry);
        $contratoactual = DB::table('eliseo.aps_empleado as a')
                    ->join('eliseo.conta_entidad as b','b.id_entidad','=','a.id_entidad')
                    ->leftjoin('eliseo.conta_entidad_depto as c','c.id_entidad',DB::raw('a.id_entidad and c.id_depto=a.id_depto'))
                    ->leftjoin('moises.persona_cuenta_bancaria as d','d.id_persona',DB::raw('a.id_persona and d.id_tipoctabanco=4 and d.activo=1'))
                    ->leftjoin('eliseo.caja_entidad_financiera as e','e.id_banco','=','d.id_banco')
                    ->leftjoin('eliseo.aps_categoria_ocupacional as f','f.id_categoriaocupacional','=','a.id_categoriaocupacional')
                    ->leftjoin('eliseo.aps_planilla as g','g.id_entidad',DB::raw('a.id_entidad and g.id_persona=a.id_persona and g.id_contrato=a.id_contrato'))
                    ->leftjoin('moises.persona_cuenta_bancaria as h','h.id_persona',DB::raw('a.id_persona and h.activo=1'))
                    ->leftjoin('eliseo.caja_entidad_financiera as i','i.id_banco','=','h.id_banco')
                    ->leftjoin('eliseo.aps_sistema_pension as j','g.id_sistemapension','=','j.id_sistemapension')
                    ->leftjoin('eliseo.conta_empresa as ce', 'b.id_empresa', '=','ce.ID_EMPRESA')
                    ->select(
                        'd.cuenta as cuenta_bancaria_cts',
                        'j.nombre as fondo_pension',
                        'e.nombre as nombre_banco_cts',
                        'h.cuenta as cuenta_bancaria',
                        'i.nombre as nombre_banco',
                        'f.nombre as situacion_laboral',
                        'g.nom_cargo as cargo',
                        'c.nombre as centro_laboral',
                        'ce.logo as logo',
                        DB::raw("to_char(a.fec_inicio,'DD/MM/YYYY') as fec_inicio,
                                    coalesce(to_char(a.fec_termino,'DD/MM/YYYY'), 'Hasta la actualidad') AS fec_termino,
                                    fc_sueldo_basico(a.id_entidad,a.id_persona,a.fec_inicio,nvl(a.fec_termino,sysdate)) as sueldo")
                       )
                       ->where('a.id_persona','=',$id_persona)
                       ->first();

        $datobrero = DB::table('eliseo.vw_aps_planilla as a')
                    ->join('eliseo.conta_entidad as b','b.id_entidad','=','a.id_entidad')
                    ->leftjoin('eliseo.conta_entidad_depto as c','c.id_entidad',DB::raw('a.id_entidad and c.id_depto=a.id_depto'))
                    ->where('a.id_persona',$id_persona)
                    ->where('a.fmr','<>',0)
                    ->select(
                            'a.id_anho',
                            'b.id_entidad',
                            'b.nombre',
                            'a.nom_cargo',
                            'a.fmr',
                            'c.nombre as depto'
                    )
                    ->distinct()
                    ->OrderBy('a.id_anho','desc')
                    ->get();

        $objparents = DB::table('moises.vw_persona_natural_light pn')
                    ->join('moises.persona_natural_parentesco as pnp','pn.id_persona','=','pnp.id_pariente')
                    ->join('moises.tipo_parentesco as tp','tp.id_tipoparentesco','=','pnp.id_tipoparentesco')
                    ->join('moises.persona_natural as d','d.id_persona','=','pn.id_persona')
                    ->leftjoin('jose.school_departamento as e','e.dep_id','=','d.id_departamento')
                    ->leftjoin('jose.school_provincia as f','f.pro_id','=','d.id_provincia')
                    ->leftjoin('jose.school_distrito as g','g.dis_id','=','d.id_distrito')
                    ->select(
                            'tp.nombre as parentesco',
                            'pnp.id_pariente',
                            'pn.paterno',
                            'pn.materno',
                            'pn.nombre',
                            'pn.num_documento',
                            'pn.nom_persona',
                            DB::raw("decode(pn.sexo, '1', 'Masculino','Femenino') as sexo,
                            to_char(pn.fec_nacimiento,'DD/MM/YYYY') as fec_nacimiento
                            ,(e.dep_nombre||case when f.pro_nombre is null then '' else '-'||f.pro_nombre end||case when g.dis_nombre is null then '' else '-'||g.dis_nombre end) as lugar_nacimiento")
                    )
                    ->where('pnp.id_persona','=',$id_persona)
                    ->whereRaw('pn.id_tipodocumento in (1,4)')
                    ->get();
                    // (SELECT floor(months_between(sysdate, to_char(pn.fec_nacimiento,'DD/MM/YYYY')) /12) FROM dual) as edad
        $objdocument=DB::table('MOISES.PERSONA_DOCUMENTO_URL as PDU')
                        ->select(
                            'PDU.ID_PERSONA',
                            'PDU.NOMBRE',
                            'PDU.URL',
                            'PDU.ID_TIPODOCUMENTO',
                            'TD.NOMBRE as TIPODOCUMENTO'
                            )
                        ->join('MOISES.TIPO_DOCUMENTO as TD','TD.ID_TIPODOCUMENTO','=','PDU.ID_TIPODOCUMENTO')
                        ->where('PDU.ID_PERSONA',$id_persona)
                        ->get();
        
        $datosdocumentos=[];
        if(!is_null($objdocument)){
            $datosdocumentos=$objdocument;
        }
        $datosparentesco=[];
        if(!is_null($objparents)){
            $datosparentesco=$objparents;
        }
        $datosobrero=[];
        if(!is_null($datobrero)){
            $datosobrero=$datobrero;
        }
        $datosemail=[];
        if(!is_null($datemail)){
            $datosemail=$datemail;
        }
        $datoscontratos=[];
        if(!is_null($datcontrato)){
            $datoscontratos=$datcontrato;
        }
      
        $datospersona=[];
        if(!is_null($objdatgen)){
            $datospersona['id_persona']=$objdatgen->id_persona;
            $datospersona['paterno']=$objdatgen->paterno;
            $datospersona['materno']=$objdatgen->materno;
            $datospersona['nombre']=$objdatgen->nombre;
            $datospersona['sexo']=$objdatgen->sexo;
            $datospersona['fec_nacimiento']=$objdatgen->fec_nacimiento;
            $datospersona['direccion']=$objdatgen->direccion;
            $datospersona['distrito']=$objdatgen->distrito;
            $datospersona['telefono']=$objdatgen->telefono;
            $datospersona['estado_civil']=$objdatgen->estado_civil;
            $datospersona['lugar_nacimiento']=$objdatgen->lugar_nacimiento;
        }else{
            $datospersona['id_persona']='';
            $datospersona['paterno']='';
            $datospersona['materno']='';
            $datospersona['nombre']='';
            $datospersona['sexo']='';
            $datospersona['fec_nacimiento']='';
            $datospersona['direccion']='';
            $datospersona['distrito']='';
            $datospersona['telefono']='';
            $datospersona['estado_civil']='';
            $datospersona['lugar_nacimiento']='';
        }
        if(!is_null($objfecmis)){
            $datospersona['fec_misionero']=$objfecmis->fec_misionero;
        }else{
            $datospersona['fec_misionero']='';
        }
        if(!is_null($email)){
            $datospersona['email']=$email->direccion;
        }else{
            $datospersona['email']='';
        }
        if(!is_null($datreligion)){
            $datospersona['religion']=$datreligion->religion;
        }else{
            $datospersona['religion']='';
        }
        
        if(!is_null($contratoactual)){
            $datospersona['cargo']=$contratoactual->cargo;
            $datospersona['situacion_laboral']=$contratoactual->situacion_laboral;
            $datospersona['centro_laboral']=$contratoactual->centro_laboral;
            $datospersona['nombre_banco']=$contratoactual->nombre_banco;
            $datospersona['cuenta_bancaria']=$contratoactual->cuenta_bancaria;
            $datospersona['fecha_inicio']=$contratoactual->fec_inicio;
            $datospersona['fecha_fin']=$contratoactual->fec_termino;
            $datospersona['sueldo']=$contratoactual->sueldo;
            $datospersona['nombre_banco_cts']=$contratoactual->nombre_banco_cts;
            $datospersona['cuenta_bancaria_cts']=$contratoactual->cuenta_bancaria_cts;
            $datospersona['num_afp']=$contratoactual->fondo_pension;
        }else{
            $datospersona['cargo']='';
            $datospersona['situacion_laboral']='';
            $datospersona['centro_laboral']='';
            $datospersona['nombre_banco']='';
            $datospersona['cuenta_bancaria']='';
            $datospersona['fecha_inicio']='';
            $datospersona['fecha_fin']='';
            $datospersona['sueldo']='';
            $datospersona['nombre_banco_cts']='';
            $datospersona['cuenta_bancaria_cts']='';
            $datospersona['num_afp']='';
        }
        if(!is_null($objdoc)){
            $datospersona['tipo_documento']=$objdoc->tipo_documento;
            $datospersona['num_documento']=$objdoc->num_documento;
            $datospersona['num_snp']=$objdoc->num_snp;
            $datospersona['num_autg']=$objdoc->num_snp;
            $datospersona['num_cuspp']=$objdoc->num_cuspp;
        }else{
            $datospersona['tipo_documento']='';
            $datospersona['num_documento']='';
            $datospersona['num_snp']='';
            $datospersona['num_autg']='';
            $datospersona['num_cuspp']='';
        }
        $datospersona['profesion']='';
        $datospersona['grado']='';
        $datospersona['numero_direccion']='';
        $datospersona['urbanizacion']='';
        //$datos =json_decode(json_encode($datos));
        $returna=[
            'datos'=>$datospersona,
            'datemail'=>$datosemail,
            'datcontrato'=>$datoscontratos,
            'datobrero'=>$datosobrero,
            'datoparentesco'=>$datosparentesco,
            'datodocumentourl'=>$datosdocumentos,
        ];
                
        return $returna;
    }
    public static function personInfoDetail($idPerson,$id_entidad)
    {
//        dd('entering', $idPerson);
        $email = DB::table('MOISES.PERSONA_VIRTUAL')
            ->where('MOISES.PERSONA_VIRTUAL.ID_PERSONA', $idPerson)
            ->select(
                'MOISES.PERSONA_VIRTUAL.DIRECCION'
            )
            ->get();
        $telefono = DB::table('MOISES.PERSONA_TELEFONO')
            ->where('MOISES.PERSONA_TELEFONO.ID_PERSONA', $idPerson)
            ->select(
                'MOISES.PERSONA_TELEFONO.NUM_TELEFONO'
            )
            ->get();
//        dd();
        $trab=DB::table('moises.trabajador')->where('id_persona',$idPerson)->where('id_entidad',$id_entidad)->select('id_condicion_laboral')->first();
        $id_condicion_laboral='-';
        if(!empty($trab)) {
            $id_condicion_laboral=$trab->id_condicion_laboral;
        }
        $data = [
            'email'=>$email->pluck('direccion'),
            'telefono'=>$telefono->pluck('num_telefono'),
            'id_condicion_laboral'=>$id_condicion_laboral
        ];
        return $data;
    }
    public static function fichapersonadni($id_dni){
        
      
        
        $objdatgen = DB::table('vw_persona_natural as a')
                    ->whereraw("a.id_persona in(select x.id_persona from MOISES.PERSONA_DOCUMENTO x where x.num_documento='".$id_dni."')")
                    ->select(
                            'a.id_persona',
                            'a.paterno',
                            'a.materno',
                            'a.nombre',
                            DB::raw("decode(a.sexo, '1', 'Masculino','Femenino') as sexo,
                            to_char(a.fec_nacimiento,'DD/MM') as fec_nacimiento"),
                            'a.nom_persona'
                    )->first();
        $id_persona=0;
        if(!is_null($objdatgen)){
            $id_persona=$objdatgen->id_persona;
        }
        
        $nerror=1;
     
        if($id_persona==0){
            $datos =[];
            $datemail=[];
            $datcontrato=[];
            $datobrero=[];
        }else{
            $objfecmis = DB::table('vw_aps_empleado')
                        ->where('id_persona',$id_persona)
                        ->select(
                                DB::raw("to_char(fec_misionero,'DD/MM/YYYY') as fec_misionero")
                           )
                           ->distinct()
                           ->first();

            $objdoc = DB::table('vw_aps_empleado as a')
                        ->join('moises.tipo_documento as b','b.id_tipodocumento','=','a.id_tipodocumento')
                        ->where('a.id_persona',$id_persona)
                        ->select(
                                'b.siglas as tipo_documento',
                                'num_documento',
                                'num_snp',
                                'num_cuspp'
                           )
                           ->distinct()
                           ->first();

            $datemail = DB::table('moises.persona_virtual as a')
                        ->join('tipo_virtual as b','b.id_tipovirtual','=','a.id_tipovirtual')
                        ->where('a.id_persona',$id_persona)
                        ->select(
                                'b.siglas as tipo_direccion',
                                'a.direccion'
                           )
                           ->OrderBy('a.id_virtual','asc')
                           ->get();


            $datcontrato = DB::table('vw_aps_empleado as a')
                        ->join('conta_entidad as b','b.id_entidad','=','a.id_entidad')
                        ->leftjoin('conta_entidad_depto as c','c.id_entidad',DB::raw('a.id_entidad and c.id_depto=a.id_depto'))
                        ->where('a.id_persona',$id_persona)
                        ->select(
                                'a.id_entidad',
                                'b.nombre',
                                'c.nombre as depto',
                                DB::raw("to_char(fec_inicio,'DD/MM/YYYY') as fec_inicio,
                                        coalesce(to_char(fec_termino,'DD/MM/YYYY'), 'Hasta la actualidad') AS fec_termino")
                           )
                           ->OrderBy('a.fec_inicio','asc')
                           ->get();

            /*$datobrero = DB::table('vw_aps_planilla as a')
                        ->join('conta_entidad as b','b.id_entidad','=','a.id_entidad')
                        ->leftjoin('conta_entidad_depto as c','c.id_entidad',DB::raw('a.id_entidad and c.id_depto=a.id_depto'))
                        ->where('a.id_persona',$id_persona)
                        ->where('a.fmr','<>',0)
                        ->select(
                                'a.id_anho',
                                'b.id_entidad',
                                'b.nombre',
                                'a.nom_cargo',
                                'a.fmr',
                                'c.nombre as depto'
                           )
                           ->distinct()
                           ->OrderBy('a.id_anho','asc')
                           ->get();*/
           
                $sql="select 
                    a.id_anho,
                    b.id_entidad,
                    b.nombre,
                    a.nom_cargo,
                    a.fmr,
                    c.nombre as depto,
                    (
                      SELECT 
                            coalesce(SUM(r.COS_VALOR),0) as sueldo
                      FROM APS_PLANILLA_DETALLE r, APS_CONCEPTO_PLANILLA d
                      WHERE r.ID_CONCEPTOAPS = d.ID_CONCEPTOAPS
                      AND r.ID_ENTIDAD = b.id_entidad 
                      AND r.ID_PERSONA = ".$id_persona."
                      AND r.ID_ANHO = a.id_anho
                      AND r.ID_MES = x.id_mes 
                      AND r.ID_CONTRATO=x.ID_CONTRATO   
                      AND r.ID_TIPOPLANILLA = 98626
                      AND d.TIPO='I'
                    )as sueldo
                    from vw_aps_planilla a
                    inner join conta_entidad b
                    on b.id_entidad=a.id_entidad
                    left join conta_entidad_depto c
                    on c.id_entidad=a.id_entidad 
                    and c.id_depto=a.id_depto
                    left join (
                      SELECT x.ID_PERSONA,
                              x.ID_ANHO,
                              MAX(x.id_mes) as id_mes,
                              x.ID_CONTRATO,
                              x.ID_ENTIDAD 
                      FROM APS_PLANILLA x
                      WHERE  x.ID_PERSONA = ".$id_persona."
                      group by x.ID_ANHO,x.ID_CONTRATO,x.ID_ENTIDAD,x.ID_PERSONA
                    ) x on x.id_persona=a.id_persona
                    and x.id_anho=a.id_anho
                    and x.id_entidad=a.id_entidad
                    where a.id_persona=".$id_persona."
                    and a.fmr<>0
                    group by a.id_anho,
                    b.id_entidad,
                    b.nombre,
                    a.nom_cargo,
                    a.fmr,
                    c.nombre,
                    x.id_mes,
                    x.ID_CONTRATO,
                    a.id_persona
                    order by a.id_anho";
            
                $datobrero = DB::select($sql);
            
                    
            
            
                        /*$objsueldo =DB::table('APS_PLANILLA')
                                   ->where('ID_PERSONA',$id_persona)
                                   ->select('ID_ANHO','ID_MES','ID_CONTRATO','ID_ENTIDAD')
                                   ->orderBy('ID_ANHO','desc')
                                   ->orderBy('ID_MES','desc')
                                   ->first();
                       
                        $sueldo=0;
                        //dd($objsueldo);
                        if(!empty($objsueldo)){
                            
                            $datasueldo = APSData::tRemuneration($objsueldo->id_entidad, $id_persona, $objsueldo->id_anho, $objsueldo->id_mes,$objsueldo->id_contrato);
                            
                            foreach($datasueldo as $row){
                                $sueldo=$row->sueldo;
                            }
                           
                        }*/


            $fec_mis ='';
            if(!is_null($objfecmis)){
                $fec_mis =$objfecmis->fec_misionero;
            }

            $tipo_documento='';
            $num_documento='';
            $num_snp='';
            $num_cuspp='';

            if(!is_null($objdoc)){
                $tipo_documento=$objdoc->tipo_documento;
                $num_documento=$objdoc->num_documento;
                $num_snp=$objdoc->num_snp;
                $num_cuspp=$objdoc->num_cuspp;
            }

            $datos =[
                'id_persona'=>$objdatgen->id_persona,
                'paterno'=>$objdatgen->paterno,
                'materno'=>$objdatgen->materno,
                'nombre'=>$objdatgen->nombre,
                'sexo'=>$objdatgen->sexo,
                'fec_nacimiento'=>$objdatgen->fec_nacimiento,
                'fec_misionero'=>$fec_mis,
                'tipo_documento'=>$tipo_documento,
                'num_documento'=>$num_documento,
                'num_snp'=>$num_snp,
                'num_cuspp'=>$num_cuspp,
                'sueldo'=>0
            ];
            $nerror=0;
        }
        //$datos =json_decode(json_encode($datos
        
        $returna=[
            'nerror'=>$nerror,
            'datos'=>$datos,
            'datemail'=>$datemail,
            'datcontrato'=>$datcontrato,
            'datobrero'=>$datobrero,
        ];
                
        return $returna;
    }

    public static function guardarPersonaDocuemntoUrl($data)
    {
        $register =DB::table('MOISES.PERSONA_DOCUMENTO_URL')
        ->select('ID_PERSONA','ID_TIPODOCUMENTO')
        ->where('ID_PERSONA','=',$data['id_persona'])
        ->where('ID_TIPODOCUMENTO','=',$data['id_tipodocumento'])
        ->get();
        if ($register->isEmpty()) {
            $result = DB::table('MOISES.PERSONA_DOCUMENTO_URL')
                ->insert($data);
        } else {
            $result=DB::table('MOISES.PERSONA_DOCUMENTO_URL')
            ->where('ID_PERSONA','=',$data['id_persona'])
            ->where('ID_TIPODOCUMENTO','=',$data['id_tipodocumento'])
            ->update($data);
        }
        return $result;
    }
    public static function eliminarPersonaDocuemntoUrl($data)
    {
        $delete =DB::table('MOISES.PERSONA_DOCUMENTO_URL')
        ->where('ID_PERSONA','=',$data['id_persona'])
        ->where('ID_TIPODOCUMENTO','=',$data['id_tipodocumento'])
        ->delete();
        return $delete;
    }
    public static function afpnet($request)
    {
        $id_empresa = $request->query('id_empresa');
        $id_entidad = $request->query('id_entidad');
        $id_anho = $request->query('id_anho');
        $id_mes = $request->query('id_mes');
        $pageSize = $request->query('pageSize');
        $search = $request->query('search');
        $query = DB::table('ELISEO.APS_EMPLEADO as E')
        ->select(
            'PED.NUM_DOCUMENTO as NUM_CUSPP',
            'PN.ID_TIPODOCUMENTO as TIPO_DOCUMENTO',
            DB::raw("(CASE WHEN PN.ID_TIPODOCUMENTO = 4 THEN lpad(PN.NUM_DOCUMENTO,9,'0') ELSE  PN.NUM_DOCUMENTO END) as NUMERO_DOCUMENTO"),
            'PN.PATERNO',
            'PN.MATERNO',
            'PN.NOMBRE',
            DB::raw("(CASE WHEN E.FEC_TERMINO IS NULL THEN 'S' WHEN TO_NUMBER($id_anho||$id_mes )>=TO_NUMBER(EXTRACT(YEAR FROM E.FEC_INICIO)||EXTRACT(MONTH FROM E.FEC_INICIO)) 
            AND TO_NUMBER($id_anho||$id_mes )<=TO_NUMBER(EXTRACT(YEAR FROM E.FEC_TERMINO)||EXTRACT(MONTH FROM E.FEC_TERMINO)) THEN 'S' ELSE  'N' END) as R_L"),
            DB::raw("(CASE WHEN TO_NUMBER(EXTRACT(YEAR FROM E.FEC_INICIO)||EXTRACT(MONTH FROM E.FEC_INICIO)) = TO_NUMBER(PD.ID_ANHO||PD.ID_MES)
            THEN 'S' ELSE 'N' END) as INICIO_RL"),
            DB::raw("(CASE WHEN E.FEC_TERMINO IS NULL THEN 'N' 
            WHEN TO_NUMBER(EXTRACT(YEAR FROM E.FEC_TERMINO)||EXTRACT(MONTH FROM E.FEC_TERMINO)) = TO_NUMBER(PD.ID_ANHO||PD.ID_MES)
            THEN 'S' ELSE 'N' END) as CESE_RL"),
            DB::raw("'' as EXCEPCION_APORTE"),
            DB::raw("CAST(PD.COS_REFERENCIA2 AS decimal(19, 2)) as BASE"),
            DB::raw("0 as APORTE,0 as A_SFP,0 as AE,'N' as RIESGO,SP.NOMBRE as AFP")
        )
        ->join('ELISEO.CONTA_ENTIDAD as CE', 'CE.ID_ENTIDAD', '=', 'E.ID_ENTIDAD')
        ->join('MOISES.VW_PERSONA_NATURAL_LIGHT as PN', 'PN.ID_PERSONA', '=', 'E.ID_PERSONA')
        ->join('ELISEO.APS_PLANILLA as P', 'E.ID_ENTIDAD', '=', 
        DB::raw('P.ID_ENTIDAD AND E.ID_PERSONA=P.ID_PERSONA AND E.ID_CONTRATO=P.ID_CONTRATO'))
        ->join('ELISEO.APS_PLANILLA_DETALLE as PD', 'P.ID_ENTIDAD', '=', 
        DB::raw('PD.ID_ENTIDAD AND P.ID_ANHO=PD.ID_ANHO AND P.ID_MES=PD.ID_MES AND P.ID_PERSONA=PD.ID_PERSONA AND P.ID_CONTRATO=PD.ID_CONTRATO'))
        ->join('ELISEO.APS_SISTEMA_PENSION as SP', 'SP.ID_SISTEMAPENSION', '=', 'P.ID_SISTEMAPENSION')
        ->leftjoin('MOISES.PERSONA_DOCUMENTO as PED', 'PED.ID_PERSONA', '=', 
        DB::raw('E.ID_PERSONA AND PED.ID_TIPODOCUMENTO=98'));
        
        if($id_entidad !== '*'){
            $query=$query->where('E.ID_ENTIDAD','=',$id_entidad);
        }

        $query=$query->where('CE.ID_EMPRESA','=',$id_empresa)
        ->where('PD.ID_ANHO','=',$id_anho)
        ->where('PD.ID_MES','=',$id_mes)
        ->where('PD.ID_CONCEPTOAPS','=','1500') // CONCEPTO DE AFP 
        ->whereRaw('PN.ID_TIPODOCUMENTO NOT IN (97,98)')
        ->orderBY('PN.PATERNO','ASC')
        ->orderBY('PN.MATERNO','ASC')
        ->orderBY('PN.NOMBRE','ASC')
        ->distinct();

        if($pageSize){
            $query = $query->paginate($pageSize);
        }else{
            $query = $query->get();
        }
        return $query;
    }

    public static function taxdistribution($id_empresa, $id_entidad, $id_anho, $id_mes)
    {
        $entidad = ($id_entidad !== "*") ? " A.ID_ENTIDAD = " . $id_entidad . " AND " : " ";

        $query = "SELECT DATOS.*,
        NVL(DATOS.RENTA_CUARTA,0) + NVL(DATOS.IMP_RENTA_QUINTA,0) + NVL(DATOS.DEV_QUINTA_CAT,0) + NVL(DATOS.ONP,0) + DATOS.ESSALUD + NVL(DATOS.EPS,0) + (NVL(DATOS.ESSALUD_VIDA,1) * NVL(DATOS.ESSALUD_VIDA_CANT,1)) AS CALCULADO,
        NVL(DATOS.RENTA_CUARTA,0) + NVL(DATOS.IMP_RENTA_QUINTA,0) + NVL(DATOS.DEV_QUINTA_CAT,0) + NVL(DATOS.ONP,0) + DATOS.ESSALUD + NVL(DATOS.EPS,0) + (NVL(DATOS.ESSALUD_VIDA,1) * NVL(DATOS.ESSALUD_VIDA_CANT,1)) AS TOTAL
                    FROM
                    (
                    SELECT DISTINCT
                        fc_nameentity(A.ID_ENTIDAD) AS ENTIDAD, 
                        NVL((SELECT SUM(HABER - DEBE) FROM VW_CONTA_DIARIO WHERE ID_CUENTAAASI = 2130341 AND ID_EMPRESA=A.ID_EMPRESA AND ID_ENTIDAD = A.ID_ENTIDAD 
		                AND ID_ANHO = A.ID_ANHO AND ID_MES=A.ID_MES),0) AS RENTA_CUARTA,
                        NVL((SELECT SUM(COS_VALOR) FROM VW_APS_PLANILLA WHERE ID_CONCEPTOAPS=1502 AND ID_EMPRESA=A.ID_EMPRESA 
                        AND ID_ENTIDAD = A.ID_ENTIDAD 
                        AND ID_ANHO = A.ID_ANHO AND ID_MES=A.ID_MES), 0) AS IMP_RENTA_QUINTA,
                        NVL((SELECT SUM(COS_VALOR) FROM VW_APS_PLANILLA WHERE ID_CONCEPTOAPS=1350 AND ID_EMPRESA=A.ID_EMPRESA 
                        AND ID_ENTIDAD = A.ID_ENTIDAD 
                        AND ID_ANHO = A.ID_ANHO AND ID_MES=A.ID_MES), 0) AS DEV_QUINTA_CAT,
                        NVL((SELECT SUM(COS_VALOR) FROM VW_APS_PLANILLA WHERE ID_CONCEPTOAPS=1522 AND ID_EMPRESA=A.ID_EMPRESA 
                        AND ID_ENTIDAD = A.ID_ENTIDAD 
                        AND ID_ANHO = A.ID_ANHO AND ID_MES=A.ID_MES),0) AS ONP, 
                        NVL((SELECT SUM(COS_VALOR) FROM VW_APS_PLANILLA WHERE ID_CONCEPTOAPS = 9000 AND ID_EMPRESA=A.ID_EMPRESA 
                        AND ID_ENTIDAD = A.ID_ENTIDAD 
                        AND ID_ANHO = A.ID_ANHO AND ID_MES=A.ID_MES), 0) AS ESSALUD, 
                        NVL((SELECT SUM(COS_VALOR) FROM VW_APS_PLANILLA WHERE ID_CONCEPTOAPS=9035 AND ID_EMPRESA=A.ID_EMPRESA 
                        AND ID_ENTIDAD = A.ID_ENTIDAD 
                        AND ID_ANHO = A.ID_ANHO AND ID_MES=A.ID_MES), 0) AS EPS,
                        NVL((SELECT SUM(COS_VALOR) FROM VW_APS_PLANILLA WHERE ID_CONCEPTOAPS = 1517 AND ID_EMPRESA=A.ID_EMPRESA 
                        AND ID_ENTIDAD = A.ID_ENTIDAD 
                        AND ID_ANHO = A.ID_ANHO AND ID_MES=A.ID_MES), 0) AS ESSALUD_VIDA,
                        NVL((SELECT COUNT(*) FROM VW_APS_PLANILLA WHERE ID_CONCEPTOAPS = 1517 AND ID_EMPRESA=A.ID_EMPRESA 
                        AND ID_ENTIDAD = A.ID_ENTIDAD 
                        AND ID_ANHO = A.ID_ANHO AND ID_MES=A.ID_MES), 0) AS ESSALUD_VIDA_CANT
                    FROM VW_APS_PLANILLA A
                    WHERE A.ID_EMPRESA = " . $id_empresa . " AND
                        " . $entidad . " A.ID_ANHO = " . $id_anho . " AND A.ID_MES = " . $id_mes . "
                    ORDER BY 1
                    ) DATOS";
                    #print_r($query);
        $oQuery = DB::select($query);
        return $oQuery;
    }



    public static function listTaxPlame($id_empresa, $id_entidad, $id_anho, $id_mes)
    {
        $entidad = ($id_entidad !== "*") ? " A.ID_ENTIDAD = " . $id_entidad . " AND " : " A.ID_ENTIDAD IS NULL AND " ;

        $query = "SELECT A.*
                    FROM PLAME_IMPUESTO A
                    WHERE A.ID_EMPRESA = " . $id_empresa . " AND
                        " . $entidad . " A.ID_ANHO = " . $id_anho . " AND A.ID_MES = " . $id_mes . "
                    ORDER BY 1";
                    //print($query);
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function addTaxPlame($request)
    {
        $result = 'ok';
        $id_empresa = $request->id_empresa;
        $id_entidad = ($request->id_entidad !== "*") ? $request->id_entidad : NULL;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $renta_cuarta = $request->renta_cuarta;
        $renta_quinta = $request->renta_quinta;
        $dev_quinta_cat = $request->dev_quinta_cat;
        $onp = $request->onp;
        $essalud = $request->essalud;
        $eps = $request->eps;
        $essalud_aporte = $request->essalud_aporte;
        $essalud_cant = $request->essalud_cant;
        $calculado = $request->calculado;
        $total = $request->total;
        $count = DB::table('PLAME_IMPUESTO')
        ->where('ID_EMPRESA','=',$id_empresa)
        ->where('ID_ANHO','=',$id_anho)
        ->where('ID_MES','=',$id_mes);
        if($id_entidad !== ''){
            $count=$count->where('ID_ENTIDAD','=',$id_entidad);
        }
        
        $count=$count->count();
        if($count == 0){
            DB::table('PLAME_IMPUESTO')->insert(
                array('ID_EMPRESA' => $id_empresa,
                    'ID_ENTIDAD' => $id_entidad,
                    'ID_ANHO' => $id_anho,
                    'ID_MES' => $id_mes,
                    'RENTA_CUARTA' => $renta_cuarta,
                    'RENTA_QUINTA' => $renta_quinta,
                    'DEV_QUINTA_CAT' => $dev_quinta_cat,
                    'ONP' => $onp,
                    'ESSALUD' => $essalud,
                    'EPS' => $eps,
                    'ESSALUD_APORTE' => $essalud_aporte,
                    'ESSALUD_CANT' => $essalud_cant,
                    'CALCULADO' => $calculado,
                    'TOTAL' => $total
                    )
            );
        } else {
            $sql = DB::table('PLAME_IMPUESTO')
            ->where('ID_EMPRESA', $id_empresa)
            ->where('ID_ENTIDAD', $id_entidad)
            ->where('ID_ANHO', $id_anho)
            ->where('ID_MES', $id_mes)
            ->update(
                array('ID_EMPRESA' => $id_empresa,
                    'ID_ENTIDAD' => $id_entidad,
                    'ID_ANHO' => $id_anho,
                    'ID_MES' => $id_mes,
                    'RENTA_CUARTA' => $renta_cuarta,
                    'RENTA_QUINTA' => $renta_quinta,
                    'DEV_QUINTA_CAT' => $dev_quinta_cat,
                    'ONP' => $onp,
                    'ESSALUD' => $essalud,
                    'EPS' => $eps,
                    'ESSALUD_APORTE' => $essalud_aporte,
                    'ESSALUD_CANT' => $essalud_cant,
                    'CALCULADO' => $calculado,
                    'TOTAL' => $total
                    )
            );
        }
        return $result;
    }
    
    public static function taxdistributionEducative($id_empresa, $id_entidad, $id_anho, $id_mes)
    {
        $entidad = ($id_entidad !== "*") ? " A.ID_ENTIDAD = " . $id_entidad . " AND " : " ";
        $depto = " ";
        if($id_empresa == 203) {
            $depto = "AND ID_DEPTO IN (121112, 121212, 121312, 121412, 121512, 129811) ";
        } elseif ($id_empresa == 204) {
            $depto = "AND ID_DEPTO IN (121112, 121212, 121312, 121412, 121512, 121612, 121712, 129811) ";
        } elseif ($id_empresa == 205) {
            $depto = "AND ID_DEPTO IN (121111, 121211, 121311, 121411, 121511, 129811) ";
        } elseif ($id_empresa == 206) {
            $depto = "AND ID_DEPTO IN (121111, 121211, 121311, 121411, 129811) ";
        } elseif ($id_empresa == 202) {
            $depto = "AND ID_DEPTO IN (121111, 121211, 121311, 121411, 121511, 121611, 121711, 121811, 129811) ";
        }

        $query = "SELECT DATOS.*,
        NVL(DATOS.RENTA_CUARTA,0) + NVL(DATOS.IMP_RENTA_QUINTA,0) + NVL(DATOS.DEV_QUINTA_CAT,0) + NVL(DATOS.ONP,0) + DATOS.ESSALUD  AS CALCULADO,
        NVL(DATOS.RENTA_CUARTA,0) + NVL(DATOS.IMP_RENTA_QUINTA,0) + NVL(DATOS.DEV_QUINTA_CAT,0) + NVL(DATOS.ONP,0) + DATOS.ESSALUD  AS TOTAL
                    FROM
                    (
                    SELECT DISTINCT
                        fc_nameentity(A.ID_ENTIDAD) AS ENTIDAD,
                        A.ID_DEPTO,
                        fc_namesdepto(A.ID_ENTIDAD, A.ID_DEPTO) AS DEPTO,
                        NVL((SELECT SUM(HABER - DEBE) FROM VW_CONTA_DIARIO WHERE ID_CUENTAAASI = 2130341 AND ID_EMPRESA=A.ID_EMPRESA AND ID_ENTIDAD = A.ID_ENTIDAD  AND ID_DEPTO = A.ID_DEPTO
		                AND ID_ANHO = A.ID_ANHO AND ID_MES=A.ID_MES), 0) AS RENTA_CUARTA,
                        NVL((SELECT SUM(COS_VALOR) FROM VW_APS_PLANILLA WHERE ID_CONCEPTOAPS=1502 AND ID_EMPRESA=A.ID_EMPRESA 
                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_DEPTO = A.ID_DEPTO
                        AND ID_ANHO = A.ID_ANHO AND ID_MES=A.ID_MES), 0) AS IMP_RENTA_QUINTA,
                        NVL((SELECT SUM(COS_VALOR) FROM VW_APS_PLANILLA WHERE ID_CONCEPTOAPS=1350 AND ID_EMPRESA=A.ID_EMPRESA 
                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_DEPTO = A.ID_DEPTO
                        AND ID_ANHO = A.ID_ANHO AND ID_MES=A.ID_MES), 0) AS DEV_QUINTA_CAT,
                        NVL((SELECT SUM(COS_VALOR) FROM VW_APS_PLANILLA WHERE ID_CONCEPTOAPS=1522 AND ID_EMPRESA=A.ID_EMPRESA 
                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_DEPTO = A.ID_DEPTO
                        AND ID_ANHO = A.ID_ANHO AND ID_MES=A.ID_MES), 0) AS ONP, 
                        NVL((SELECT SUM(COS_VALOR) FROM VW_APS_PLANILLA WHERE ID_CONCEPTOAPS = 9000 AND ID_EMPRESA=A.ID_EMPRESA 
                        AND ID_ENTIDAD = A.ID_ENTIDAD AND ID_DEPTO = A.ID_DEPTO
                        AND ID_ANHO = A.ID_ANHO AND ID_MES=A.ID_MES), 0) AS ESSALUD
                    FROM VW_APS_PLANILLA A
                    WHERE A.ID_EMPRESA = " . $id_empresa . " AND
                        " . $entidad . "
                        A.ID_ANHO = " . $id_anho . " AND
                        A.ID_MES = " . $id_mes . "
                        " . $depto . "
                    ORDER BY 1
                    ) DATOS";
                    #print($query);
        $oQuery = DB::select($query);
        return $oQuery;
    }

    public static function getYearByEntity($data)
    {
        $existe = DB::table('moises.persona_documento_url')
        ->where('id_persona',$data->id_persona)
        ->where('id_tipodocumento',$data->id_tipodocumento)
        ->select('id_persona','id_tipodocumento')
        ->first();
        try
        {
            if($existe->id_persona){
                $result = DB::table('moises.persona_documento_url')
                ->where('id_persona',$data->id_persona)
                ->where('id_tipodocumento',$data->id_tipodocumento)
                ->update($data);
            }else{
                $result = DB::table('moises.persona_documento_url')
                ->insert($data);
            }
        }
        catch(Exception $e)
        {
            die($e->getMessage());
            $result = false;
        }
        return $result;
    }

    public static function getDatosGenrales($request)
    {
        $id_persona = $request->query('id_persona');
        $results=[];
        if($id_persona && $id_persona!=null && $id_persona!='null'){
        try
        {
            if(!is_numeric($id_persona)) {
                $id_persona=null;
            }
            $results = DB::table('MOISES.PERSONA as P')
            ->join('MOISES.PERSONA_NATURAL as PN','PN.ID_PERSONA','=','P.ID_PERSONA')
            ->leftjoin('MOISES.PERSONA_DOCUMENTO as PD','PD.ID_PERSONA','=','P.ID_PERSONA')
            ->leftjoin('MOISES.PERSONA_VIRTUAL as PV','PV.ID_PERSONA','=','P.ID_PERSONA')
            ->leftjoin('MOISES.PERSONA_TELEFONO as PT','PT.ID_PERSONA','=','P.ID_PERSONA')
            ->leftjoin('MOISES.PERSONA_DIRECCION as PDI','PDI.ID_PERSONA','=','P.ID_PERSONA')
            ->leftjoin('TIPO_ESTADO_CIVIL as TEC','TEC.ID_TIPOESTADOCIVIL','=','PN.ID_TIPOESTADOCIVIL')
            ->leftjoin('TIPO_PAIS as TP','TP.ID_TIPOPAIS','=','PN.ID_TIPOPAIS')
            ->select('P.ID_PERSONA','TEC.NOMBRE as ESTADOCIVIL',
            DB::raw("UPPER (NVL (P.PATERNO || ' ', '')|| NVL (P.MATERNO || ', ', ''))||P.NOMBRE as NOM_PERSONA"),
            DB::raw("CASE WHEN PN.SEXO=1 THEN 'Masculino' ELSE 'Femenino' END as sexo,
            TO_CHAR(PN.FEC_NACIMIENTO, 'YYYY-MM-DD') as FEC_NACIMIENTO"),
            'PD.NUM_DOCUMENTO','PV.DIRECCION as CORREO',
            'PT.NUM_TELEFONO','PDI.DIRECCION','TP.GENTILICIO as NACIONALIDAD')
            ->where('P.ID_PERSONA','=',$id_persona)
            ->first();
        }
        catch(Exception $e)
        {
            $result = [];
        }
    }
        return $results;
    }
    public static function getEmpleadoByEntity($request)
    {
        $search = $request->query('search');
        $id_entidad = $request->query('id_entidad');
        $results=[];
        try
        {
            $results = DB::table('VW_APS_EMPLEADO')
            ->select('ID_PERSONA','NOM_PERSONA')
            ->whereRaw("ID_ENTIDAD={$id_entidad} 
            AND (UPPER(NUM_DOCUMENTO) LIKE UPPER('%{$search}%') OR UPPER(PATERNO) LIKE UPPER('%{$search}%') 
            OR UPPER(MATERNO) like UPPER('%{$search}%') OR UPPER(NOMBRE) LIKE UPPER('%{$search}%'))")
            ->distinct()
            ->get();
        }
        catch(Exception $e)
        {
            $result = [];
        }
        return $results;
    }

    public static function getTipoArchivo($request) {
        $grupo_archivo = $request->grupo_archivo;
        $search = $request->search;
        $query = DB::table('TIPO_ARCHIVO');
        if($search!==null){
            $query=$query->whereRaw("(NOMBRE LIKE '%".$search."%' OR UPPER(NOMBRE) LIKE UPPER('%".$search."%'))");
        }
        $query=$query->select('*')
        ->get();
        return $query;
    }

    public static function addTipoArchivo($request) {
        $nombre = $request->nombre;
        $abreviatura = $request->abreviatura;
        $id_grupoarchivo = $request->id_grupoarchivo;
        DB::table('TIPO_ARCHIVO')
            ->insert(
                array('NOMBRE' => $nombre,
                    'ABREVIATURA' => $abreviatura,
                    'ID_GRUPOARCHIVO' => $id_grupoarchivo
                )
            );  
        $query = DB::table('TIPO_ARCHIVO')
        ->select('*')
        ->where('NOMBRE',$nombre)
        ->where('ABREVIATURA',$abreviatura)
        ->where('ID_GRUPOARCHIVO',$id_grupoarchivo)
        ->first();
        return $query;
    }


    public static function getResponsibleReporting($request) {
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $pageSize = $request->pageSize;
        
        if($id_depto === NULL or $id_depto ==='' or $id_depto ==='*' ){
            $id_depto = 0;
        }

        $c_date = $id_anho.'-'.$id_mes.'-01 23:59:00';
        $fecha = new DateTime($c_date);
        $fecha->modify('last day of this month');
        $fech_last = $fecha->format('yy-m-d');

        $query = DB::table('ARCHIVO_MENSUAL_RESP A')
        ->join('MOISES.VW_PERSONA_NATURAL_LIGHT B', 'A.ID_PERSONA', '=', 'B.ID_PERSONA')
        ->select('A.ID_ENTIDAD',
        'A.ID_DEPTO',
        'A.ID_PERSONA',
        DB::raw("FC_OBT_NAME_ENTIDAD_DEPTO(A.ID_ENTIDAD, A.ID_DEPTO) as NAME_ENTI_DEP"),
        'A.FEC_INICIO',
        'A.FEC_TERMINO',
        'B.NOM_PERSONA'
        );
        if( $id_entidad !== 'null' AND $id_entidad !== null AND $id_entidad !=='*'){
            $query=$query->where('A.ID_ENTIDAD','=',$id_entidad)
                        ->where('A.ID_DEPTO', '=', $id_depto); 
        }
        $query=$query->whereRaw("A.FEC_INICIO <= TO_DATE('". $fech_last."', 'YYYY-MM-DD') AND (A.FEC_TERMINO >= TO_DATE('".$id_anho."-".$id_mes."-01', 'YYYY-MM-DD')  OR A.FEC_TERMINO IS NULL)")
        ->orderBy('A.ID_ENTIDAD','DESC')
        ->orderBy('A.FEC_INICIO','ASC')->paginate($pageSize);
        return $query;
    }


    public static function addResponsibleReporting($request) {
        $ret='OK';
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_persona = $request->id_persona;

        if($id_depto === NULL or $id_depto ==='' or $id_depto ==='*' ){
            $id_depto = 0;
        }

        $sql = "SELECT ID_ENTIDAD, ID_DEPTO, FEC_INICIO, ID_PERSONA FROM ARCHIVO_MENSUAL_RESP 
        where ID_ENTIDAD = ".$id_entidad." AND ID_DEPTO = ".$id_depto." AND FEC_TERMINO IS NULL";
        $oQuery = DB::select($sql);
        $contar = 0;
        foreach ($oQuery as $row){
            $contar++;
        }

        if($contar == 0){
            DB::table('ARCHIVO_MENSUAL_RESP')->insert(
                array('ID_ENTIDAD' => $id_entidad,
                    'ID_DEPTO' => $id_depto,
                    'ID_PERSONA' => $id_persona,
                    'FEC_INICIO' => Carbon::now(),
                    )
            );  
        }else{
            foreach ($oQuery as $row){
                DB::table('ARCHIVO_MENSUAL_RESP')
                ->where('ID_ENTIDAD', $row->id_entidad)
                ->where('ID_DEPTO', $row->id_depto)
                ->where('ID_PERSONA', $row->id_persona)
                ->where('FEC_INICIO', $row->fec_inicio)
                ->update(
                    array(
                        'FEC_TERMINO' => Carbon::now(),
                        )
                );  
            }
            DB::table('ARCHIVO_MENSUAL_RESP')->insert(
                array('ID_ENTIDAD' => $id_entidad,
                    'ID_DEPTO' => $id_depto,
                    'ID_PERSONA' => $id_persona,
                    'FEC_INICIO' => Carbon::now(),
                    )
            );   
        }

        return $ret;

    }

    public static function disabledResponsibleReporting($request) {
        $ret='OK';
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_persona = $request->id_persona;
        $fec_inicio = $request->fec_inicio;

        if($id_depto === NULL or $id_depto ==='' ){
            $id_depto = 0;
        }
        DB::table('ARCHIVO_MENSUAL_RESP')
        ->where('ID_ENTIDAD', $id_entidad)
        ->where('ID_DEPTO', $id_depto)
        ->where('ID_PERSONA', $id_persona)
        ->where('FEC_INICIO', $fec_inicio)
        ->update(
            array(
                'FEC_TERMINO' => Carbon::now(),
                )
        );
        return $ret;
    }

    public static function deleteResponsibleReporting($request) {
        $ret='OK';
        $id_entidad = $request->id_entidad;
        $id_depto = $request->id_depto;
        $id_persona = $request->id_persona;
        $fec_inicio = $request->fec_inicio;
        DB::table('ARCHIVO_MENSUAL_RESP')
                ->where('ID_ENTIDAD', $id_entidad)
                ->where('ID_DEPTO', $id_depto)
                ->where('ID_PERSONA', $id_persona)
                ->where('FEC_INICIO', $fec_inicio)
                ->delete();
        return $ret;
    }


    public static function getConfigMonthlyControl($request) {
        $id_empresa = $request->id_empresa;
        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        if($id_empresa==='all'){
            $id_empresa=null;
        }
        if($id_entidad==='all'){
            $id_entidad=null;
        }
        $query = DB::table('ARCHIVO_MENSUALGTH A')
        ->join('TIPO_ARCHIVO B', 'A.ID_TIPOARCHIVO', '=', 'B.ID_TIPOARCHIVO')
        ->leftjoin('VW_CONTA_EMPRESA C', 'A.ID_EMPRESA', '=', 'C.ID_EMPRESA')
        ->leftjoin('VW_CONTA_ENTIDAD D', 'A.ID_ENTIDAD', '=', 'D.ID_ENTIDAD')
        ->select(
            'A.ID_ARCHIVO_GTH',
            DB::raw("NVL(C.NOM_EMPRESA,'GENERAL') AS EMPRESA"),
            DB::raw("NVL(D.NOMBRE,'GENERAL') AS ENTIDAD"),
            'B.NOMBRE AS TIPODOCUMENTO',
            'A.ID_EMPRESA',
            'A.ID_ENTIDAD',
            'A.ID_TIPOARCHIVO',
            'A.ID_ANHO',
            'A.ID_MES',
            'A.FECHA_LIMITE'
            );
        if($id_empresa===null || $id_empresa!=='*'){
            $query=$query->where('A.ID_EMPRESA',$id_empresa);
        }
        if($id_entidad===null || $id_entidad!=='*'){
            $query=$query->where('A.ID_ENTIDAD',$id_entidad);
        }
        $query=$query->where('A.ID_ANHO',$id_anho)
        ->where('A.ID_MES',$id_mes)
        ->orderBy('A.ID_EMPRESA','DESC')
        ->orderBy('A.ID_ENTIDAD','DESC')
        ->orderBy('A.ID_ANHO','ASC')
        ->orderBy('A.ID_MES','ASC')
        ->orderBy('A.FECHA_LIMITE','ASC')
        ->get();
        return $query;
    }
    public static function addConfigMonthlyControl($request) {
        $res="OK";
        $id_empresa = $request->id_empresa;
        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $id_tipoarchivo = $request->id_tipoarchivo;
        $fecha_limite = $request->fecha_limite;
        if($id_empresa===null || $id_empresa==='*' || $id_empresa==='all'){
            DB::table('ARCHIVO_MENSUALGTH')
                ->where('ID_ANHO', $id_anho)
                ->where('ID_MES', $id_mes)
                ->where('ID_TIPOARCHIVO', $id_tipoarchivo)
                ->delete();
            $id_empresa=null;
        }
        if($id_entidad===null || $id_entidad==='*' || $id_entidad==='all'){
            if($id_empresa!==null and $id_empresa!=='*' and  $id_empresa!=='all'){
                DB::table('ARCHIVO_MENSUALGTH')
                    ->where('ID_ANHO', $id_anho)
                    ->where('ID_MES', $id_mes)
                    ->where('ID_EMPRESA', $id_empresa)
                    ->where('ID_TIPOARCHIVO', $id_tipoarchivo)
                    ->delete();
            }
            $id_entidad=null;
        }
        DB::table('ARCHIVO_MENSUALGTH')
            ->updateOrInsert(
                array('ID_EMPRESA'=>$id_empresa,
                    'ID_ENTIDAD' => $id_entidad,
                    'ID_ANHO' => $id_anho,
                    'ID_MES'=>$id_mes,
                    'ID_TIPOARCHIVO' => $id_tipoarchivo
            ),
                array('FECHA_LIMITE' => $fecha_limite
                )
            );  
        return $res;
    }
    public static function editConfigMonthlyControl($id_archivo_gth,$request) {
        $res="OK";
        $id_empresa = $request->id_empresa;
        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $id_tipoarchivo = $request->id_tipoarchivo;
        $fecha_limite = $request->fecha_limite;
        if($id_empresa===null || $id_empresa==='*' || $id_empresa==='all'){
            DB::table('ARCHIVO_MENSUALGTH')
                ->where('ID_ANHO', $id_anho)
                ->where('ID_MES', $id_mes)
                ->where('ID_TIPOARCHIVO', $id_tipoarchivo)
                ->where('ID_ARCHIVO_GTH','<>', $id_archivo_gth)
                ->delete();
            $id_empresa=null;
        }
        if($id_entidad===null || $id_entidad==='*' || $id_entidad==='all'){
            if($id_empresa!==null and $id_empresa!=='*' and  $id_empresa!=='all'){
                DB::table('ARCHIVO_MENSUALGTH')
                    ->where('ID_ANHO', $id_anho)
                    ->where('ID_MES', $id_mes)
                    ->where('ID_EMPRESA', $id_empresa)
                    ->where('ID_TIPOARCHIVO', $id_tipoarchivo)
                    ->where('ID_ARCHIVO_GTH','<>', $id_archivo_gth)
                    ->delete();
            }
            $id_entidad=null;
        }
        DB::table('ARCHIVO_MENSUALGTH')
            ->where('ID_ARCHIVO_GTH', $id_archivo_gth)
            ->update(
                array('ID_EMPRESA'=>$id_empresa,
                    'ID_ENTIDAD' => $id_entidad,
                    'ID_ANHO' => $id_anho,
                    'ID_MES'=> $id_mes,
                    'ID_TIPOARCHIVO' => $id_tipoarchivo,
                    'FECHA_LIMITE' => $fecha_limite
                    )
            );  
        return $res;
    }
    public static function deleteConfigMonthlyControl($id) {
        $res="OK";
        DB::table('ARCHIVO_MENSUALGTH')
                ->where('ID_ARCHIVO_GTH', $id)
                ->delete();
        return $res;
    }
    public static function getEntityById($id_entidad) {
        $query = DB::table('CONTA_ENTIDAD A')
        ->join('MOISES.PERSONA B', 'A.ID_PERSONA', '=', 'B.ID_PERSONA')
        ->where("A.ID_ENTIDAD", "=",$id_entidad)
        ->where("A.ES_ACTIVO", "=",1)
        ->select(
            'A.ID_PERSONA',
            'A.NOMBRE AS ENTIDAD',
            'A.ID_EMPRESA',
            'A.ID_ENTIDAD',
            'B.NOMBRE',
            'B.PATERNO',
            'B.MATERNO'
        )
        ->first();
        return $query;
    }

    public static function getMonthlyControl($request,$id_empresa) {
        $id_grupoarchivo = $request->id_grupoarchivo;
        $id_entidad = $request->id_entidad;
        $id_anho = $request->id_anho;
        $id_mes = $request->id_mes;
        $sql_entidad = '';
    
        $query = DB::table('ARCHIVO_MENSUALGTH A')
        ->join('TIPO_ARCHIVO B', 'A.ID_TIPOARCHIVO', '=', 'B.ID_TIPOARCHIVO')
        ->join('GRUPO_ARCHIVO C', 'B.ID_GRUPOARCHIVO', '=', 'C.ID_GRUPOARCHIVO')
        ->leftjoin('VW_CONTA_ENTIDAD D', 'A.ID_ENTIDAD', '=', 'D.ID_ENTIDAD')
        ->leftjoin('ARCHIVO_GTH_DETALLE E', 'A.ID_ARCHIVO_GTH', '=', DB::raw("E.ID_ARCHIVO_GTH AND E.ID_ENTIDAD=D.ID_ENTIDAD"))
        ->leftjoin('MOISES.VW_PERSONA_NATURAL_LIGHT PN', 'E.ID_USER', '=', 'PN.ID_PERSONA')
        ->select(
            'C.ID_GRUPOARCHIVO',
            'C.NOMBRE AS GRUPOARCHIVO',
            'B.NOMBRE AS TIPOARCHIVO',
            'A.ID_ARCHIVO_MENSUAL',
            'A.ID_EMPRESA',
            'A.ID_ENTIDAD',
            'A.ID_TIPOARCHIVO',
            'A.ID_ANHO',
            'A.ID_MES',
            'A.FECHA_LIMITE',
            'E.ID_DETALLE',
            'E.FECHA_CREACION',
            'E.FECHA_MODIFICACION',
            'E.URL AS FILE_URL',
            'E.NOMBRE AS FILE',
            'E.FORMATO',
            'E.TAMANHO',
            'E.ID_USER',
            'PN.NOM_PERSONA AS USER'
            );

        $query=$query->where('A.ID_ENTIDAD',$id_entidad)
        ->where('A.ID_ANHO',$id_anho);
        if($id_mes!==null && $id_mes!=='*'){
            $query=$query->where('A.ID_MES',$id_mes);
        }
        if($id_grupoarchivo!==null && $id_grupoarchivo!=='*'){
            $query=$query->where('B.ID_GRUPOARCHIVO',$id_grupoarchivo);
        }
        if($id_grupoarchivo===null or $id_grupoarchivo==='*'){
            $id_grupoarchivo=" IS NOT NULL";
        }else{
            $id_grupoarchivo=" = $id_grupoarchivo";
        }

        if($id_entidad !=='null' && $id_entidad!==null && $id_entidad!=='*'){
            $sql_entidad = " AND D.ID_ENTIDAD = $id_entidad ";
        }


        $query="SELECT C.ID_GRUPOARCHIVO,C.NOMBRE AS GRUPOARCHIVO,B.NOMBRE AS TIPOARCHIVO,A.ID_ARCHIVO_GTH,
        A.ID_EMPRESA,A.ID_ENTIDAD,A.ID_TIPOARCHIVO,A.ID_ANHO,A.ID_MES,A.FECHA_LIMITE,D.ID_ARCHIVO_GTH_DETALLE,
        D.FECHA_CREACION,D.FECHA_MODIFICACION,D.URL AS FILE_URL,D.NOMBRE AS FILE_NOMBRE,D.FORMATO,D.TAMANHO,D.ID_USER,PN.NOM_PERSONA AS USER_NAME
        FROM  ARCHIVO_MENSUALGTH A
        INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO=B.ID_TIPOARCHIVO
        INNER JOIN GRUPO_ARCHIVO C ON B.ID_GRUPOARCHIVO=C.ID_GRUPOARCHIVO
        LEFT JOIN ARCHIVO_GTH_DETALLE D ON A.ID_ARCHIVO_GTH=D.ID_ARCHIVO_GTH $sql_entidad
        LEFT JOIN MOISES.VW_PERSONA_NATURAL_LIGHT PN ON D.ID_USER=PN.ID_PERSONA
        WHERE A.ID_ENTIDAD=$id_entidad
        AND A.ID_ANHO = $id_anho
        AND A.ID_MES = $id_mes
        AND B.ID_GRUPOARCHIVO  $id_grupoarchivo
        
        UNION ALL
        
        SELECT C.ID_GRUPOARCHIVO,C.NOMBRE AS GRUPOARCHIVO,B.NOMBRE AS TIPOARCHIVO,A.ID_ARCHIVO_GTH,
        A.ID_EMPRESA,A.ID_ENTIDAD,A.ID_TIPOARCHIVO,A.ID_ANHO,A.ID_MES,A.FECHA_LIMITE,D.ID_ARCHIVO_GTH_DETALLE,
        D.FECHA_CREACION,D.FECHA_MODIFICACION,D.URL AS FILE_URL,D.NOMBRE AS FILE_NOMBRE,D.FORMATO,D.TAMANHO,D.ID_USER,PN.NOM_PERSONA AS USER_NAME
        FROM  ARCHIVO_MENSUALGTH A
        INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO=B.ID_TIPOARCHIVO
        INNER JOIN GRUPO_ARCHIVO C ON B.ID_GRUPOARCHIVO=C.ID_GRUPOARCHIVO
        LEFT JOIN ARCHIVO_GTH_DETALLE D ON A.ID_ARCHIVO_GTH=D.ID_ARCHIVO_GTH $sql_entidad
        LEFT JOIN MOISES.VW_PERSONA_NATURAL_LIGHT PN ON D.ID_USER=PN.ID_PERSONA
        WHERE A.ID_EMPRESA = $id_empresa 
        AND A.ID_ENTIDAD IS NULL
        AND A.ID_ANHO = $id_anho
        AND A.ID_MES = $id_mes
        AND B.ID_GRUPOARCHIVO  $id_grupoarchivo
        
        UNION ALL 
        
        SELECT C.ID_GRUPOARCHIVO,C.NOMBRE AS GRUPOARCHIVO,B.NOMBRE AS TIPOARCHIVO,A.ID_ARCHIVO_GTH,
        A.ID_EMPRESA,A.ID_ENTIDAD,A.ID_TIPOARCHIVO,A.ID_ANHO,A.ID_MES,A.FECHA_LIMITE,D.ID_ARCHIVO_GTH_DETALLE,
        D.FECHA_CREACION,D.FECHA_MODIFICACION,D.URL AS FILE_URL,D.NOMBRE AS FILE_NOMBRE,D.FORMATO,D.TAMANHO,D.ID_USER,PN.NOM_PERSONA AS USER_NAME
        FROM  ARCHIVO_MENSUALGTH A
        INNER JOIN TIPO_ARCHIVO B ON A.ID_TIPOARCHIVO=B.ID_TIPOARCHIVO
        INNER JOIN GRUPO_ARCHIVO C ON B.ID_GRUPOARCHIVO=C.ID_GRUPOARCHIVO
        LEFT JOIN ARCHIVO_GTH_DETALLE D ON A.ID_ARCHIVO_GTH=D.ID_ARCHIVO_GTH $sql_entidad
        LEFT JOIN MOISES.VW_PERSONA_NATURAL_LIGHT PN ON D.ID_USER=PN.ID_PERSONA
        WHERE A.ID_EMPRESA IS NULL
        AND A.ID_ENTIDAD IS NULL
        AND A.ID_ANHO = $id_anho
        AND A.ID_MES = $id_mes
        AND B.ID_GRUPOARCHIVO  $id_grupoarchivo
        ORDER BY GRUPOARCHIVO,TIPOARCHIVO,ID_ANHO,ID_MES,FECHA_LIMITE";
        //print($query);
        $result= DB::select($query);
        return $result;
    }
    public static function uploadMonthlyControl($request,$file,$id_user) {
        $res="OK";
        $id_archivo_gth = $request->id_archivo_gth;
        $id_entidad = $request->id_entidad;
        $fecha_creacion = $request->fecha_creacion;
        $fecha = new DateTime();
        if($fecha_creacion===null){
            $fecha_creacion=$fecha;
        }
        DB::table('ARCHIVO_GTH_DETALLE')
            ->updateOrInsert(
                array('ID_ARCHIVO_GTH' => $id_archivo_gth,
                    'ID_ENTIDAD' => $id_entidad
            ),
                array(
                    'FECHA_CREACION'=>$fecha_creacion,
                    'FECHA_MODIFICACION'=>$fecha,
                    'URL' => $file['url'],
                    'NOMBRE' => $file['filename'],
                    'TAMANHO'=>$file['size'],
                    'FORMATO'=>$file['format'],
                    'ID_USER'=>$id_user
                )
            );  
        return $res;
    }
    public static function deleteFileMonthlyControl($request,$id_user) {
        $id_archivo_gth_detalle = $request->id_archivo_gth_detalle;
        $ret='OK';
        $fecha = new DateTime();
        DB::table('ARCHIVO_GTH_DETALLE')
                ->where('ID_ARCHIVO_GTH_DETALLE', $id_archivo_gth_detalle)
                ->update(
                    array(
                        'FECHA_MODIFICACION'=>$fecha,
                        'URL' =>null,
                        'NOMBRE' => null,
                        'TAMANHO'=>null,
                        'FORMATO'=>null,
                        'ID_USER'=>$id_user
                    )
                );
        return $ret;   
    }

}

