<?php
namespace App\Http\Data\FinancesStudent;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SetupenrollmentData extends Controller {
    
    public static  function correlativo($tabla,$columna,$pcolumna1=array(), $pcolumna2=array()){
        
        $q=DB::table($tabla);
        if(count($pcolumna1)>0){
            $q->whereraw($pcolumna1['column'].'='.$pcolumna1['valor']);
        }
        if(count($pcolumna2)>0){
            $q->whereraw($pcolumna2['column'].'='.$pcolumna2['valor']);
        }
        $column=" coalesce(max(".$columna."),0)+1 as correlativo ";

        $q->select(DB::raw($column));
        
        $data=$q->get();

        
        $id = 0;
        foreach($data as $row){
             $id = $row->correlativo;
        }
        return $id;
    }
    public static function listTypeModality(){
//        dd('geting');
//        $oQuery = DB::table('tipo_modalidad')
//                  ->get();
        $oQuery = DB::table('david.TIPO_NIVEL_ENSENANZA')
            ->get();
        return $oQuery;

    }
    //plan de pagos
    public static function listPaymentPlan(){

        $oQuery =DB::table('mat_planpago as a')
                ->select('a.*',DB::raw("case when a.estado=1 then 'Activo' else 'Inactivo' end as estado_desc"))
                ->orderBy('a.nombre','asc')
                 ->paginate(20);

        return $oQuery;
    }
    public static function showPaymentPlan($id_planpago){                
        $oQuery = DB::table('mat_planpago')
                ->where('id_planpago',$id_planpago)
               ->get();
        return $oQuery;
    }
    public static function addPaymentPlan($params){    
        
        
        $id_planpago  = SetupenrollmentData::correlativo('mat_planpago','id_planpago');
       
        $data= DB::table('MAT_PLANPAGO')->insert(
            array('ID_PLANPAGO'=>$id_planpago,
                'NOMBRE' => $params->nombre,
                'CODIGO' => $params->codigo,
                'DESCRIPCION' => $params->descripcion,
                'CUOTAS'=> $params->cuotas,      
                'CUOTA_CRO'=> $params->cuota_cro,
                'conmat1cuota'=> $params->conmat1cuota,                
                'ESTADO'=>'1'
                )
        );   
        
        $oQuery = DB::table('mat_planpago')
                ->where('id_planpago',$id_planpago)
               ->get();
        return $oQuery;

    }
    public static function updatePaymentPlan($id_planpago,$params){                 
        
        
        DB::table('MAT_PLANPAGO')
            ->where('ID_PLANPAGO', $id_planpago)
            ->update(
                    ['NOMBRE' => $params->nombre,
                    'CODIGO' => $params->codigo,
                    'DESCRIPCION' => $params->descripcion,
                    'CUOTAS'=> $params->cuotas,
                    'CUOTA_CRO'=> $params->cuota_cro,                 
                    'conmat1cuota'=> $params->conmat1cuota, 
                    'ESTADO'=>$params->estado]
                    );
        
         $oQuery = DB::table('mat_planpago')
                ->where('id_planpago',$id_planpago)
               ->get();
        return $oQuery;       

    }
    public static function deletePaymentPlan($id_planpago){ 
        
        DB::table('MAT_PLANPAGO')->where('ID_PLANPAGO', $id_planpago)->delete();
        
       
    }
    //configuracion de parametros
     public static function listConfigParameter($nombre){

        $oQuery =DB::table('mat_config_parametro as a')
                ->whereraw("lower(a.nombre) like lower('%".$nombre."%') ")
                ->select('a.*',DB::raw("case when a.estado=1 then 'Activo' else 'Inactivo' end as estado_desc"))
                 ->orderBy('a.nombre','asc')
                 ->paginate(20);

        return $oQuery;
    }
    public static function showConfigParameter($id_config_parametro){                
        $oQuery = DB::table('mat_config_parametro')
                ->where('id_config_parametro',$id_config_parametro)
               ->get();
        return $oQuery;
    }
    public static function addConfigParameter($params){    
        
        
        $id_config_parametro  = SetupenrollmentData::correlativo('mat_config_parametro','id_config_parametro');
       
        $data= DB::table('MAT_CONFIG_PARAMETRO')->insert(
            array('ID_CONFIG_PARAMETRO'=>$id_config_parametro,
                'NOMBRE' => $params->nombre,
                'DESCRIPCION'=>$params->descripcion,
                'CODIGO' => $params->codigo,
                'ESTADO'=>'1'
                )
        );   
        
        $oQuery = DB::table('mat_config_parametro')
                ->where('id_config_parametro',$id_config_parametro)
               ->get();
        return $oQuery;

    }
    public static function updateConfigParameter($id_config_parametro,$params){                 
        
        
        DB::table('MAT_CONFIG_PARAMETRO')
            ->where('ID_CONFIG_PARAMETRO', $id_config_parametro)
            ->update(
                    ['NOMBRE' => $params->nombre,
                    'CODIGO' => $params->codigo,
                    'DESCRIPCION'=>$params->descripcion,
                    'ESTADO'=>$params->estado]
                    );
        
        $oQuery = DB::table('mat_config_parametro')
                ->where('id_config_parametro',$id_config_parametro)
               ->get();
        return $oQuery;       

    }
    public static function deleteConfigParameter($id_config_parametro){ 
        
        DB::table('MAT_CONFIG_PARAMETRO')->where('id_config_parametro', $id_config_parametro)->delete();
        
       
    }
    //criterio de matricula
     public static function listEnrollmentCriterion($id_modalidad,$nombre){
         
         $child =DB::table('mat_criterio as a')
                ->where('a.id_modalidad',$id_modalidad)
                ->where('a.id_parent',0)
                ->whereraw("a.id_criterio in(select x.id_parent from mat_criterio x where x.id_modalidad=".$id_modalidad." and lower(x.nombre) like lower('%".$nombre."%'))")
                ->select('a.id_criterio',
                        'a.id_modalidad',
                        'a.codigo',
                        'a.nombre',
                        'a.tipo_cobro',
                        'a.id_parent',
                        'a.dc',
                        'a.estado',
                        DB::raw("case when a.estado=1 then 'Activo' else 'Inactivo' end as estado_desc,case when a.tipo_cobro='M' then 'Mensual' else 'Unico' end as tipo_cobro_desc"));

         
        $oQuery =DB::table('mat_criterio as a')
                ->whereraw("lower(a.nombre) like lower('%".$nombre."%') ")
                ->where('a.id_modalidad',$id_modalidad)
                ->where('a.id_parent',0)
                ->select('a.id_criterio',
                        'a.id_modalidad',
                        'a.codigo',
                        'a.nombre',
                        'a.tipo_cobro',
                        'a.id_parent',
                        'a.dc',
                        'a.estado',
                        DB::raw("case when a.estado=1 then 'Activo' else 'Inactivo' end as estado_desc,case when a.tipo_cobro='M' then 'Mensual' else 'Unico' end as tipo_cobro_desc"))
                ->union($child)
                ->orderBy('nombre','asc')
                ->get();
        
        $data=array();
        foreach ($oQuery as $row) {
            $item=array();
            $item['id_criterio']=$row->id_criterio;
            $item['id_modalidad']=$row->id_modalidad;
            $item['codigo']=$row->codigo;
            $item['nombre']=$row->nombre;
            $item['tipo_cobro']=$row->tipo_cobro;
            $item['tipo_cobro_desc']=$row->tipo_cobro_desc;
            $item['id_parent']=$row->id_parent;
            $item['dc']=$row->dc;
            $item['estado']=$row->estado;
            $item['estado_desc']=$row->estado_desc;
            $datitems =DB::table('mat_criterio as a')
                ->where('a.id_modalidad',$id_modalidad)
                ->where('a.id_parent',$row->id_criterio)
                ->whereraw("lower(a.nombre) like lower('%".$nombre."%')")
                ->select('a.*',DB::raw("case when a.estado=1 then 'Activo' else 'Inactivo' end as estado_desc,case when a.tipo_cobro='M' then 'Mensual' else 'Unico' end as tipo_cobro_desc"))
                ->orderBy('a.nombre','asc')
                ->get();
            $item['items']=$datitems;
            $data[]=$item;
        }
                   

        return $data;
    }
    public static function showEnrollmentCriterion($id_criterio){                
        $oQuery = DB::table('mat_criterio')
                ->where('id_criterio',$id_criterio)
               ->get();
        return $oQuery;
    }
    public static function addEnrollmentCriterion($params){    
        
        
        $id_criterio  = SetupenrollmentData::correlativo('mat_criterio','id_criterio');
       
        $data= DB::table('MAT_CRITERIO')->insert(
            array('ID_CRITERIO'=>$id_criterio,
                'ID_MODALIDAD' => $params->id_modalidad,
                'CODIGO'=>$params->codigo,
                'NOMBRE' => $params->nombre,
                'TIPO_COBRO' => $params->tipo_cobro,
                'ID_PARENT' => $params->id_parent,
                'DC' => $params->dc,
                'ESTADO'=>'1'
                )
        );   
        
        
        $oQuery = DB::table('mat_criterio')
                ->where('id_criterio',$id_criterio)
               ->get();
        return $oQuery;

    }
    public static function updateEnrollmentCriterion($id_criterio,$params){                 
        
        
        DB::table('MAT_CRITERIO')
            ->where('ID_CRITERIO', $id_criterio)
            ->update(
                    ['ID_MODALIDAD' => $params->id_modalidad,
                    'CODIGO'=>$params->codigo,
                    'TIPO_COBRO' => $params->tipo_cobro,
                    'NOMBRE' => $params->nombre,
                    'ID_PARENT' => $params->id_parent,
                    'DC' => $params->dc,
                    'ESTADO'=>$params->estado]
                    );
        
        $oQuery = DB::table('mat_criterio')
                ->where('id_criterio',$id_criterio)
               ->get();
        return $oQuery;        

    }
    public static function deleteEnrollmentCriterion($id_criterio){ 
        
        DB::table('MAT_CRITERIO')->where('id_criterio', $id_criterio)->delete();
        
       
    }
    
}

