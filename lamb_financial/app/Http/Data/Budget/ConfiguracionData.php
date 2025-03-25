<?php
namespace App\Http\Data\Budget;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
class ConfiguracionData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function listProject($id_entidad,$id_depto){
        $query = "SELECT 
                    ID_PROYECTO,
                    ID_ENTIDAD,
                    NOMBRE,
                    COMENTARIO,
                    TO_CHAR(FDESDE,'DD/MM/YYYY') AS FDESDE ,	
                    TO_CHAR(FHASTA,'DD/MM/YYYY') AS FHASTA ,
                    ESTADO,
                    ID_DEPTO,
                    CASE WHEN ESTADO='1' THEN 'Activo' ELSE 'Inactivo' end ESTADO_DESC
            FROM PSTO_PROYECTO
            WHERE ID_ENTIDAD=".$id_entidad."
            AND ID_DEPTO='".$id_depto."'
            ORDER BY NOMBRE";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showProject($id_proyecto){                
        $query = "SELECT 
                    ID_PROYECTO,
                    ID_ENTIDAD,
                    NOMBRE,
                    COMENTARIO,
                    FDESDE ,	
                    FHASTA ,
                    ESTADO,
                    ID_DEPTO
            FROM PSTO_PROYECTO
            WHERE ID_PROYECTO=".$id_proyecto."";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function addProject($id_entidad,$nombre,$comentario,$fdesde,$fhasta,$id_depto,$estado){    
        
        $query = "SELECT 
                        COALESCE(MAX(ID_PROYECTO),0)+1 ID_PROYECTO
                FROM PSTO_PROYECTO ";  
        $oQuery = DB::select($query);
        $id_proyecto = 0;
        foreach ($oQuery as $key => $item){
            $id_proyecto = $item->id_proyecto;                
        }
        if($id_proyecto==0){
            $id_proyecto = 1;
        }
        
        $data= DB::table('PSTO_PROYECTO')->insert(
            array('ID_PROYECTO'=>$id_proyecto,
                'ID_ENTIDAD' => $id_entidad,
                'ID_DEPTO' => $id_depto,
                'NOMBRE'=> $nombre,                
                'COMENTARIO'=> $comentario,
                'FDESDE'=> $fdesde,
                'FHASTA'=> $fhasta,
                'ESTADO'=> $estado
                )
        );   
        
        $query = "SELECT 
                    ID_PROYECTO,
                    ID_ENTIDAD,
                    NOMBRE,
                    COMENTARIO,
                    FDESDE ,	
                    FHASTA ,
                    ESTADO,
                    ID_DEPTO
            FROM PSTO_PROYECTO
            WHERE ID_PROYECTO=".$id_proyecto."";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function updateProject($id_proyecto,$nombre,$comentario,$fdesde,$fhasta,$estado){                 
        
        $query = "UPDATE PSTO_PROYECTO SET 
                    NOMBRE = '".$nombre."',
                    COMENTARIO = '".$comentario."',
                    FDESDE = '".$fdesde."',
                    FHASTA = '".$fhasta."',
                    ESTADO = '".$estado."'
              WHERE ID_PROYECTO = ".$id_proyecto;
        DB::update($query);
        
        $query = "SELECT 
                    ID_PROYECTO,
                    ID_ENTIDAD,
                    NOMBRE,
                    COMENTARIO,
                    FDESDE ,	
                    FHASTA ,
                    ESTADO,
                    ID_DEPTO
            FROM PSTO_PROYECTO
            WHERE ID_PROYECTO=".$id_proyecto."";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function deleteProject($id_proyecto){ 
        $query = "DELETE FROM PSTO_PROYECTO
                WHERE ID_PROYECTO = ".$id_proyecto;
        DB::delete($query);
    }
    
    public static function listEvent($id_entidad,$id_depto,$id_proyecto,$id_auxiliar){
        $where="";
        if(strlen($id_proyecto)==0){
            $id_proyecto=0;
        }
        if($id_proyecto>0){
            $where=" AND E.ID_PROYECTO=".$id_proyecto."";
        }
        if(strlen($id_auxiliar)>0){
            $where=" AND E.ID_AUXILIAR=".$id_auxiliar."";
        }
        $query = "SELECT 
                    E.ID_EVENTO ,
                    E.ID_PROYECTO ,
                    E.ID_ENTIDAD ,
                    E.NOMBRE ,
                    E.ID_AUXILIAR,
                    E.DESCRIPCION,
                    E.CANTIDAD, 
                    E.PUNIDAD,
                    E.A ,
                    E.B ,
                    E.C , 
                    E.D ,
                    E.E ,
                    E.F ,
                    E.G , 
                    E.FORMULA,
                    E.TIPO_ASIENTO,
                    E.ESTADO,
                    CASE WHEN E.ESTADO='1' THEN 'Activo' ELSE 'Inactivo' end ESTADO_DESC,
                    P.NOMBRE AS PROYECTO,
                    A.NOMBRE AS AUXILIAR,
                    E.ID_DEPTO
            FROM PSTO_EVENTO E INNER JOIN PSTO_PROYECTO P ON
            E.ID_PROYECTO=P.ID_PROYECTO 
            LEFT JOIN PSTO_AUXILIAR A ON 
            A.ID_AUXILIAR=E.ID_AUXILIAR
            WHERE E.ID_ENTIDAD=".$id_entidad."
            AND E.ID_DEPTO='".$id_depto."' 
            ".$where."
            ORDER BY E.NOMBRE";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showEvent($id_evento){                
        $query = "SELECT 
                    E.ID_EVENTO ,
                    E.ID_PROYECTO ,
                    E.ID_ENTIDAD ,
                    E.NOMBRE ,
                    E.ID_AUXILIAR,
                    E.DESCRIPCION,
                    E.CANTIDAD, 
                    E.PUNIDAD,
                    E.A ,
                    E.B ,
                    E.C , 
                    E.D ,
                    E.E ,
                    E.F ,
                    E.G  ,
                    E.FORMULA,
                    E.TIPO_ASIENTO,
                    E.ESTADO,
                    P.NOMBRE AS PROYECTO,
                    A.NOMBRE AS AUXILIAR,
                    E.ID_DEPTO
            FROM PSTO_EVENTO E INNER JOIN PSTO_PROYECTO P ON
            E.ID_PROYECTO=P.ID_PROYECTO 
            LEFT JOIN PSTO_AUXILIAR A ON 
            A.ID_AUXILIAR=E.ID_AUXILIAR
            WHERE E.ID_EVENTO=".$id_evento."";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function addEvent($id_proyecto,$id_entidad,$id_depto,$nombre,$id_auxiliar,$descripcion,$cantidad,$punidad,$a,$b,$c,$d,$e,$f,$g,$formula,$tipo_asiento,$estado){  
        
        $query = "SELECT 
                        COALESCE(MAX(ID_EVENTO),0)+1 ID_EVENTO
                FROM PSTO_EVENTO";  
        $oQuery = DB::select($query);
        $id_evento = 0;
        foreach ($oQuery as $key => $item){
            $id_evento = $item->id_evento;                
        }

        if($id_evento==0){
            $id_evento = 1;
        }
        
        $data= DB::table('PSTO_EVENTO')->insert(
            array('ID_EVENTO'=>$id_evento,
                'ID_PROYECTO' => $id_proyecto,
                'ID_ENTIDAD'=> $id_entidad,                
                'NOMBRE'=> $nombre,
                'ID_AUXILIAR'=> $id_auxiliar,
                'DESCRIPCION'=> $descripcion,
                'CANTIDAD'=> $cantidad,
                'PUNIDAD'=> $punidad,
                'A'=> $a,
                'B'=> $b,
                'C'=> $c,
                'D'=> $d,
                'E'=> $e,
                'F'=> $f,
                'G'=> $g,
                'FORMULA'=> $formula,
                'TIPO_ASIENTO'=>$tipo_asiento,
                'ESTADO'=> $estado,
                'ID_DEPTO'=> $id_depto
                )
        );
        
        $query = "SELECT 
                    ID_EVENTO ,
                    ID_PROYECTO ,
                    ID_ENTIDAD ,
                    NOMBRE ,
                    ID_AUXILIAR,
                    DESCRIPCION,
                    CANTIDAD, 
                    PUNIDAD,
                    A ,
                    B ,
                    C , 
                    D ,
                    E ,
                    F ,
                    G , 
                    FORMULA,
                    TIPO_ASIENTO,
                    ESTADO,
                    ID_DEPTO
            FROM PSTO_EVENTO
            WHERE ID_EVENTO=".$id_evento."";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function updateEvent($id_evento,$nombre,$id_auxiliar,$descripcion,$cantidad,$punidad,$a,$b,$c,$d,$e,$f,$g,$formula,$tipo_asiento,$estado){                 
        
        $query = "UPDATE PSTO_EVENTO SET 
                    NOMBRE = '".$nombre."',
                    ID_AUXILIAR = '".$id_auxiliar."',
                    DESCRIPCION = '".$descripcion."',
                    CANTIDAD = '".$cantidad."',
                    PUNIDAD = '".$punidad."',
                    A = '".$a."',
                    B = '".$b."',
                    C = '".$c."',
                    D = '".$d."',
                    E = '".$e."',
                    F = '".$f."',
                    G = '".$g."',
                    FORMULA = '".$formula."',
                    ESTADO = '".$estado."',
                    TIPO_ASIENTO='".$tipo_asiento."'
              WHERE ID_EVENTO = ".$id_evento;
        DB::update($query);
        
        $query = "SELECT 
                    ID_EVENTO ,
                    ID_PROYECTO ,
                    ID_ENTIDAD ,
                    NOMBRE ,
                    ID_AUXILIAR,
                    DESCRIPCION,
                    CANTIDAD, 
                    PUNIDAD,
                    A ,
                    B ,
                    C , 
                    D ,
                    E ,
                    F ,
                    G , 
                    FORMULA,
                    TIPO_ASIENTO,
                    ESTADO
            FROM PSTO_EVENTO
            WHERE ID_EVENTO=".$id_evento."";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function deleteEvent($id_evento){ 
        $query = "DELETE FROM PSTO_EVENTO
                WHERE ID_EVENTO = ".$id_evento;
        DB::delete($query);
    }
    
    public static function listActivity($id_evento){

        $query = "SELECT 
                    A.ID_ACTIVIDAD,
                    A.ID_EVENTO, 
                    A.TIPO,
                    CASE WHEN A.TIPO='I' THEN 'Ingreso' ELSE 'Gasto' end as TIPO_DESC,
                    A.NOMBRE, 
                    A.ESDESCUENTO , 
                    CASE WHEN A.ESDESCUENTO='S' THEN 'Si' ELSE '' end as ESDESCUENTO_DESC,
                    A.DESCRIPCION ,
                    A.ID_TIPOPLAN ,
                    A.ID_CUENTAAASI , 
                    A.ID_RESTRICCION , 
                    A.ID_ENTIDAD , 
                    A.ID_CTACTE , 
                    A.ID_TIPOCTACTE , 
                    A.IMPORTEUNIT1 , 
                    A.IMPORTEUNIT2 ,
                    A.ID_DEPTO,
                    A.ESTADO,
                    A.ID_ENTIDAD_CTACTE,
                    CASE WHEN A.ESTADO='1' THEN 'Activo' ELSE 'Inactivo' end as ESTADO_DESC,
                    A.TIPO_DIST,
                    CASE WHEN A.TIPO_DIST='PR' THEN 'Proporcional' WHEN A.TIPO_DIST='PO' THEN 'Porcentaje' ELSE '' end as TIPO_DIST_DESC,
                    FC_PSTO_DIST_MESNSUAL(A.ID_ACTIVIDAD) AS MESES
            FROM PSTO_ACTIVIDAD A,PSTO_EVENTO E
            WHERE A.ID_EVENTO=E.ID_EVENTO
            AND A.ID_EVENTO=".$id_evento."
            ORDER BY A.TIPO DESC,A.ID_ACTIVIDAD";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showActivity($id_actividad){
        $query = "SELECT 
                    a.ID_ACTIVIDAD,
                    a.ID_EVENTO, 
                    a.TIPO,
                    a.NOMBRE, 
                    a.ESDESCUENTO , 
                    a.DESCRIPCION ,
                    a.ID_TIPOPLAN ,
                    a.ID_CUENTAAASI , 
                    a.ID_RESTRICCION , 
                    a.ID_ENTIDAD , 
                    a.ID_CTACTE , 
                    a.ID_TIPOCTACTE , 
                    a.IMPORTEUNIT1 , 
                    a.IMPORTEUNIT2 , 
                    a.ID_DEPTO,
                    a.ESTADO,
                    c.NOMBRE AS DEPARTAMENTO,
                    b.NOMBRE AS DENOMINACIONAL,
                    a.ID_ENTIDAD_CTACTE,
                    a.TIPO_DIST
            FROM PSTO_ACTIVIDAD a LEFT JOIN CONTA_CTA_DENOMINACIONAL b ON
            b.ID_TIPOPLAN=a.ID_TIPOPLAN 
            AND b.ID_CUENTAAASI=a.ID_CUENTAAASI 
            AND b.ID_RESTRICCION=a.ID_RESTRICCION 
            LEFT JOIN CONTA_ENTIDAD_DEPTO c ON
            c.ID_ENTIDAD=a.ID_ENTIDAD  
            AND c.ID_DEPTO=a.ID_DEPTO  
            WHERE a.ID_ACTIVIDAD=".$id_actividad;
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function addActivity($id_evento,$tipo,$nombre,$esdescuento,$descripcion,$id_tipoplan,$id_cuentaaasi,$id_restriccion,$id_entidad,$id_ctacte,$id_tipoctacte,$importeunit1,$importeunit2,$id_depto,$estado,$tipo_dist){
        
        $query = "SELECT 
                        COALESCE(MAX(ID_ACTIVIDAD),0)+1 ID_ACTIVIDAD
                FROM PSTO_ACTIVIDAD";  
        $oQuery = DB::select($query);
        $id_actividad = 0;
        foreach ($oQuery as $key => $item){
            $id_actividad = $item->id_actividad;                
        }
        
        if($id_actividad==0){
            $id_actividad = 1;
        }
        
        $data= DB::table('PSTO_ACTIVIDAD')->insert(
            array('ID_ACTIVIDAD'=>$id_actividad,
                'ID_EVENTO'=> $id_evento,                
                'TIPO'=> $tipo,
                'NOMBRE'=> $nombre,
                'ESDESCUENTO'=> $esdescuento,
                'DESCRIPCION'=> $descripcion,
                'ID_TIPOPLAN'=> $id_tipoplan,
                'ID_CUENTAAASI'=> $id_cuentaaasi,
                'ID_RESTRICCION'=> $id_restriccion,
                'ID_ENTIDAD'=> $id_entidad,
                'ID_CTACTE'=> $id_ctacte,
                'ID_TIPOCTACTE'=> $id_tipoctacte,
                'IMPORTEUNIT1'=> $importeunit1,
                'IMPORTEUNIT2'=> $importeunit2,
                'ID_DEPTO'=>$id_depto,
                'ESTADO'=> $estado,
                'TIPO_DIST'=>$tipo_dist
                )
        );
        
        
        
        $query = "SELECT 
                    ID_ACTIVIDAD,
                    ID_EVENTO, 
                    TIPO,
                    NOMBRE, 
                    ESDESCUENTO , 
                    DESCRIPCION ,
                    ID_TIPOPLAN ,
                    ID_CUENTAAASI , 
                    ID_RESTRICCION , 
                    ID_ENTIDAD , 
                    ID_CTACTE , 
                    ID_TIPOCTACTE , 
                    IMPORTEUNIT1 , 
                    IMPORTEUNIT2 , 
                    ID_DEPTO,
                    ESTADO ,
                    ID_ENTIDAD_CTACTE,
                    TIPO_DIST
            FROM PSTO_ACTIVIDAD
            WHERE ID_ACTIVIDAD=".$id_actividad;
        $oQuery = DB::select($query);
        return $oQuery;
    
        
    }
    public static function updateActivity($id_actividad,$tipo,$nombre,$esdescuento,$descripcion,$id_tipoplan,$id_cuentaaasi,$id_restriccion,$id_entidad,$id_ctacte,$id_tipoctacte,$importeunit1,$importeunit2,$id_depto,$estado,$asigna,$id_entidad_ctacte,$tipo_dist){
        
              
        $query = "UPDATE PSTO_ACTIVIDAD SET 
                    TIPO = '".$tipo."',
                    NOMBRE = '".$nombre."',
                    ESDESCUENTO = '".$esdescuento."',
                    DESCRIPCION = '".$descripcion."',
                    ID_TIPOPLAN = '".$id_tipoplan."',
                    ID_CUENTAAASI = '".$id_cuentaaasi."',
                    ID_RESTRICCION = '".$id_restriccion."',
                    ID_ENTIDAD = '".$id_entidad."',
                    ID_CTACTE = '".$id_ctacte."',
                    ID_TIPOCTACTE = '".$id_tipoctacte."',
                    IMPORTEUNIT1 = '".$importeunit1."',
                    IMPORTEUNIT2= '".$importeunit2."',
                    ID_DEPTO='".$id_depto."',
                    ESTADO = '".$estado."',
                    ID_ENTIDAD_CTACTE = '".$id_entidad_ctacte."' ,
                    TIPO_DIST='".$tipo_dist."' 
              WHERE ID_ACTIVIDAD = ".$id_actividad;
        
        DB::update($query);
        
        
        if($asigna=="S"){
            
            $sql="SELECT ID_EVENTO FROM PSTO_ACTIVIDAD WHERE ID_ACTIVIDAD = ".$id_actividad;
            
            $oQuery = DB::select($sql); 
            $id_evento=0;
            foreach ($oQuery as $row){
                $id_evento=$row->id_evento;
            }
            
            
            $query = "UPDATE PSTO_ACTIVIDAD SET 
                        ID_TIPOPLAN = '".$id_tipoplan."',
                        ID_CUENTAAASI = '".$id_cuentaaasi."',
                        ID_RESTRICCION = '".$id_restriccion."',
                        ID_ENTIDAD = '".$id_entidad."',
                        ID_CTACTE = '".$id_ctacte."',
                        ID_TIPOCTACTE = '".$id_tipoctacte."',
                        ID_DEPTO='".$id_depto."',
                        ID_ENTIDAD_CTACTE = '".$id_entidad_ctacte."'
                  WHERE ID_EVENTO  = ".$id_evento." 
                  AND TIPO = '".$tipo."'";
            
            DB::update($query);
            
        }
        
        $query = "SELECT 
                    ID_ACTIVIDAD,
                    ID_EVENTO, 
                    TIPO,
                    NOMBRE, 
                    ESDESCUENTO , 
                    DESCRIPCION ,
                    ID_TIPOPLAN ,
                    ID_CUENTAAASI , 
                    ID_RESTRICCION , 
                    ID_ENTIDAD , 
                    ID_CTACTE , 
                    ID_TIPOCTACTE , 
                    IMPORTEUNIT1 , 
                    IMPORTEUNIT2 , 
                    ID_DEPTO,
                    ESTADO,
                    ID_ENTIDAD_CTACTE,
                    TIPO_DIST
            FROM PSTO_ACTIVIDAD
            WHERE ID_ACTIVIDAD=".$id_actividad;
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function deleteActivity($id_actividad){
        $query = "DELETE FROM PSTO_ACTIVIDAD
                WHERE ID_ACTIVIDAD = ".$id_actividad;
        DB::delete($query);
    }
    
    public static function listActivityDist($id_actividad){
        $query = "SELECT 
                    A.ID_MES,
                    A.NOMBRE,
                    COALESCE(B.PORCENTAJE,0) AS PORCENTAJE,
                    CASE WHEN B.ID_ACTIVIDAD IS NULL THEN '0' ELSE '1' END AS INDICADOR
            FROM CONTA_MES A LEFT JOIN PSTO_ACTIVIDAD_DIST B
            ON A.ID_MES=B.ID_MES
            AND B.ID_ACTIVIDAD=".$id_actividad." 
            ORDER BY A.ID_MES";
        $oQuery = DB::select($query); 
        $data=array();
        foreach($oQuery as $row){
            $item=array();
            $item["id_mes"]=$row->id_mes;
            $item["nombre"]=$row->nombre;
            $item["porcentaje"]=$row->porcentaje;
            $val=false;
            if($row->indicador=="1"){
                $val=true;
            }
            $item["indicador"]=$val;
            $data[]=$item;
         }
        return $data;
    }
    
    public static function updateActivityDist($id_actividad,$details){
        
        $query = "SELECT 
                    TIPO_DIST 
            FROM PSTO_ACTIVIDAD
            WHERE ID_ACTIVIDAD=".$id_actividad;
        $oQuery = DB::select($query); 
        $tipo_dist='PR';
        foreach($oQuery as $row){
            $tipo_dist=$row->tipo_dist;
        }
        $porcentaje=0;
        
        foreach($details as $param){
            
            if(($param->indicador==true) and ($param->porcentaje>0)){
   
                $porcentaje=$porcentaje + $param->porcentaje;
            }
        }
        $ret=0;
        if($tipo_dist=='PO'){
            if($porcentaje<>100){
                $ret=1;
            }
        }
        
        if($ret==0){
            $query = "DELETE FROM PSTO_ACTIVIDAD_DIST  WHERE ID_ACTIVIDAD=".$id_actividad;
            $oQuery = DB::delete($query); 

            DB::delete($query);

            foreach($details as $param){
                
                if($tipo_dist=="PO"){
                    if(($param->indicador==true) and ($param->porcentaje>0)){


                        $data= DB::table('PSTO_ACTIVIDAD_DIST')->insert(
                        array('ID_ACTIVIDAD'=>$id_actividad,
                            'ID_MES' => $param->id_mes,
                            'PORCENTAJE'=> $param->porcentaje
                            )
                        ); 


                    }
                }else{
                    if($param->indicador==true){


                        $data= DB::table('PSTO_ACTIVIDAD_DIST')->insert(
                        array('ID_ACTIVIDAD'=>$id_actividad,
                            'ID_MES' => $param->id_mes,
                            'PORCENTAJE'=>0
                            )
                        ); 


                    }
                }
            }
        }
        return $ret;
    }
    
    public static function listArea($id_entidad){
        $query = "SELECT 
                    ID_AREA ,
                    ID_ENTIDAD, 
                    ID_DEPTO , 
                    NOMBRE , 
                    ESTADO 
            FROM PSTO_AREA
            WHERE ID_ENTIDAD=".$id_entidad."
            ORDER BY NOMBRE";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showArea($id_area){
        $query = "SELECT 
                    ID_AREA ,
                    ID_ENTIDAD, 
                    ID_DEPTO , 
                    NOMBRE , 
                    ESTADO 
            FROM PSTO_AREA
            WHERE ID_AREA=".$id_area."
            ORDER BY NOMBRE";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function addArea($id_entidad,$id_depto,$nombre,$estado){
        
        $query = "SELECT 
                        COALESCE(MAX(ID_AREA),0)+1 ID_AREA
                FROM PSTO_AREA ";  
        $oQuery = DB::select($query);
        $id_area  = 0;
        foreach ($oQuery as $key => $item){
            $id_area  = $item->id_area ;                
        }
        if($id_area==0){
            $id_area = 1;
        }
        
        $data= DB::table('PSTO_AREA')->insert(
            array('ID_AREA'=>$id_area,
                'ID_ENTIDAD' => $id_entidad,
                'ID_DEPTO'=> $id_depto,                
                'NOMBRE'=> $nombre,
                'ESTADO'=> $estado
                )
        );   
        
        $query = "SELECT 
                    ID_AREA ,
                    ID_ENTIDAD, 
                    ID_DEPTO , 
                    NOMBRE , 
                    ESTADO
            FROM PSTO_AREA
            WHERE ID_AREA=".$id_area."";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function updateArea($id_area,$id_entidad,$id_depto,$nombre,$estado){
        
        $query = "UPDATE PSTO_AREA SET 
                    ID_ENTIDAD = ".$id_entidad.",
                    ID_DEPTO = '".$id_depto."',
                    NOMBRE = '".$nombre."',
                    ESTADO = '".$estado."'
              WHERE ID_AREA = ".$id_area;
        DB::update($query);
        
        $query = "SELECT 
                    ID_AREA ,
                    ID_ENTIDAD, 
                    ID_DEPTO , 
                    NOMBRE , 
                    ESTADO
            FROM PSTO_AREA
            WHERE ID_AREA=".$id_area."";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function deleteArea($id_area){
        $query = "DELETE FROM PSTO_AREA
                WHERE ID_AREA = ".$id_area;
        DB::delete($query);
    }

    public static function listEjeActivo(){
        $query = "SELECT 
                    ID_EJE ,
                    NOMBRE , 
                    ESTADO 
            FROM PSTO_EJE
            WHERE ESTADO='1'
            ORDER BY ID_EJE";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listUnitNegocioActivo(){
        $query = "SELECT 
                    ID_PSTONEGOCIO ,
                    NOMBRE , 
                    ESTADO 
            FROM PSTO_NEGOCIO
            WHERE ESTADO='1'
            ORDER BY NOMBRE";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listAreaActivo($id_entidad){
        $query = "SELECT 
                    ID_AREA ,
                    ID_ENTIDAD,
                    ID_DEPTO,
                    NOMBRE , 
                    ESTADO 
            FROM PSTO_AREA
            WHERE ID_ENTIDAD=".$id_entidad." 
            AND ESTADO='1'
            ORDER BY NOMBRE";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listProyectoActivo($id_entidad,$id_depto){
        $query = "SELECT 
                    ID_PROYECTO ,
                    ID_ENTIDAD,
                    NOMBRE,
                    COMENTARIO ,
                    FDESDE,
                    FHASTA,
                    ESTADO,
                    ID_DEPTO
            FROM PSTO_PROYECTO
            WHERE  ID_ENTIDAD=".$id_entidad." 
            AND ID_DEPTO='".$id_depto."' 
            AND ESTADO='1'
            ORDER BY NOMBRE";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listAuxliarActivo($id_entidad,$id_depto){
        $query = "SELECT 
                    ID_AUXILIAR ,
                    ID_ENTIDAD,
                    NOMBRE,
                    URL ,
                    ESTADO,
                    ID_DEPTO
            FROM PSTO_AUXILIAR
            WHERE  ID_ENTIDAD=".$id_entidad." 
            AND ID_DEPTO='".$id_depto."' 
            AND ESTADO='1'
            ORDER BY NOMBRE";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    
    public static function listCtaCte($id_entidad,$id_tipoplan,$id_cuentaaasi,$id_restriccion)
    {
           
        $query = "SELECT 
                ID_TIPOCTACTE 
                FROM CONTA_CTA_DENOMINACIONAL b
                WHERE ID_TIPOPLAN=".$id_tipoplan."
                AND ID_CUENTAAASI='".$id_cuentaaasi."'
                AND ID_RESTRICCION='".$id_restriccion."'";
        $oQuery = DB::select($query);
        
        $id_tipoctacte="";
        foreach($oQuery as $row){
            $id_tipoctacte=$row->id_tipoctacte;
        }
        
        
         $query = "SELECT 
                   ID_CTACTE ,ID_TIPOCTACTE,NOMBRE
                FROM CONTA_ENTIDAD_CTA_CTE
                WHERE ID_TIPOCTACTE='".$id_tipoctacte."'
                AND ID_ENTIDAD=".$id_entidad." 
                ORDER BY NOMBRE";
   
        $oQuery = DB::select($query);

        return $oQuery;
    }
    public static function listEventProyecto($id_proyecto){
        $query = "SELECT 
                    ID_EVENTO ,
                    NOMBRE,
                    DESCRIPCION 
            FROM PSTO_EVENTO
            WHERE ESTADO='1'
            AND ID_PROYECTO=".$id_proyecto."
            ORDER BY NOMBRE";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listDeptoActivo($id_entidad,$buscar){
        $query = "SELECT 
                ID_DEPTO,
                NOMBRE
                FROM CONTA_ENTIDAD_DEPTO 
                WHERE ID_ENTIDAD=".$id_entidad."
                AND ES_GRUPO='0' 
                AND ES_ACTIVO='1'
                AND (LOWER(NOMBRE) LIKE LOWER('%".$buscar."%') OR  ID_DEPTO LIKE '".$buscar."%')
                ORDER BY ID_DEPTO";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listDeptoFac($id_entidad,$id_depto){
        $query = "SELECT 
                    ID_AREA_PADRE,
                    AREA_PADRE
                FROM VW_AREA_DEPTO  
                WHERE ID_ENTIDAD=".$id_entidad."
                AND ID_DEPTO_AREA=$id_depto
                AND ID_TIPO_AREA='FA'
                AND ID_TIPO_DEPTO='EP'
                GROUP BY ID_AREA_PADRE,AREA_PADRE
                ORDER BY AREA_PADRE";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listMenecion($id_entidad,$id_depto,$tipo){
        $query = "SELECT 
                        ID_EAP,
                        NOMBRE
                    FROM PSTO_EAP 
                    WHERE ID_ENTIDAD=".$id_entidad."
                    AND ID_DEPTO='".$id_depto."'
                    AND ID_TIPO_DEPTO='".$tipo."'
                    ORDER BY NOMBRE";
        $oQuery = DB::select($query);        
        return $oQuery;
    }

    public static function listDeptoPregrado($id_entidad,$id_depto,$tipo,$tipo_depto){
        
        if($tipo=="PG"){
            $query = "SELECT 
                        ID_AREA AS ID_DEPTO,
                        AREA AS NOMBRE 
                        FROM VW_AREA_DEPTO 
                        WHERE ID_ENTIDAD=".$id_entidad."
                        AND ID_DEPTO_AREA='".$id_depto."'
                        AND ID_TIPO_DEPTO='".$tipo_depto."'
                        AND ID_TIPO_AREA='".$tipo."'
                        GROUP BY ID_AREA,AREA
                        ORDER BY NOMBRE"; 
        }else{
            $query = "SELECT 
                        ID_DEPTO,
                        DEPARTAMENTO AS NOMBRE 
                    FROM VW_AREA_DEPTO 
                    WHERE ID_ENTIDAD=".$id_entidad."
                    AND ID_DEPTO_AREA='".$id_depto."'
                    AND ID_TIPO_DEPTO='".$tipo_depto."'
                    AND ID_TIPO_AREA='".$tipo."'
                    ORDER BY NOMBRE";
        }
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    
    public static function listAreaDepto($id_entidad,$id_depto,$id_param,$tipo){
        
        $selec=" id_depto as cod,departamento as nombre ";
        $group="";
        $where=" ";
        if($tipo=="A"){
            $selec=" id_area_padre as cod,area_padre as nombre ";
            $group=" group by id_area_padre,area_padre ";
            $where=" ";
        }
        if($tipo=="B"){
            $selec=" id_area as cod,area as nombre ";
            $group=" group by id_area,area ";
            $where=" and id_parent=".$id_param;
        }
        if($tipo=="C"){
            $selec=" id_depto as cod,departamento as nombre ";
            $group="";
            $where=" and id_area=".$id_param;
        }
        if($tipo=="D"){
            $where=" and ( lower(departamento) like '%".$id_param."%' OR id_depto like '%".$id_param."%') ";
        }
        $query = "SELECT 
                        ".$selec." 
                    FROM vw_area_depto
                    WHERE id_entidad=".$id_entidad."
                    AND id_depto_area='".$id_depto."' 
                    ".$where." 
                    ".$group."  
                    ORDER BY nombre";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
  public static function listarDeptoActvo($id_proyecto){
      
    $query = "SELECT 
                        id_entidad,
                        id_depto
                    FROM psto_proyecto
                    WHERE id_proyecto=".$id_proyecto;
    $oQuery = DB::select($query);  
    $id_entidad = 0;
    $id_depto ='';
    foreach($oQuery as $row){
        $id_entidad = $row->id_entidad;
        $id_depto =$row->id_depto;
    }
    $query = "SELECT 
                        id_depto,
                        nombre,
                        id_parent
                    FROM conta_entidad_depto
                    WHERE id_entidad=".$id_entidad."
                    AND SUBSTR(id_depto,1,1)='".$id_depto."' 
                    AND length(id_depto)=8 
                    AND es_activo='1'  
                    AND es_grupo='0'
                    ORDER BY id_depto";
        $oQuery = DB::select($query);        
        return $oQuery;
    }

  public static function listRenovable(){
        
        $query = "SELECT 
                    ID_RENOVABLE,
                    NOMBRE 
                FROM  PSTO_PLLA_RENOVABLE 
                WHERE ESTADO='1'
                ORDER BY NOMBRE";
      
        $oQuery = DB::select($query);        
        return $oQuery;

    }
    public static function listSexo(){
        
        $query = "SELECT 
                    ID_SEXO,
                    NOMBRE 
                FROM  APS_SEXO 
                WHERE ESTADO='1'
                ORDER BY NOMBRE";
      
        $oQuery = DB::select($query);        
        return $oQuery;

    }
    public static function listEdad(){
        
        $query = "SELECT 
                    ID_EDAD,
                    NOMBRE 
                FROM  PSTO_PLLA_EDAD 
                WHERE ESTADO='1'
                ORDER BY ID_EDAD";
      
        $oQuery = DB::select($query);        
        return $oQuery;

    }
    public static function listNivelEducativo($estado_psto=''){
        
        $where="";
        if(strlen($estado_psto)>0){
            $where=" AND ESTADO_PSTO='1'";
        }
        
        $query = "SELECT 
                    ID_NIVEL_EDU,
                    NOMBRE 
                FROM  APS_NIVEL_EDUCATIVO 
                WHERE ESTADO='1'
                ".$where."
                ORDER BY ID_NIVEL_EDU";
      
        $oQuery = DB::select($query);        
        return $oQuery;

    }
    public static function listEstadoCivil($estado_psto=''){
        
      
        $where="";
        if(strlen($estado_psto)>0){
            $where=" AND ESTADO_PSTO='1'";
        }
        
        $query = "SELECT 
                    ID_TIPOESTADOCIVIL,
                    NOMBRE ,
                    NOMBRE_CORTO
                FROM  TIPO_ESTADO_CIVIL 
                WHERE ESTADO='1'
                ".$where."
                ORDER BY ID_TIPOESTADOCIVIL";
      
        $oQuery = DB::select($query);        
        return $oQuery;

    }
    public static function listTiempoTrabajo(){
        
        $query = "SELECT 
                    ID_TIEMPOTRABAJO,
                    NOMBRE 
                FROM  APS_TIEMPO_TRABAJO 
                WHERE ESTADO='1'
                ORDER BY NOMBRE";
      
        $oQuery = DB::select($query);        
        return $oQuery;

    }
    public static function listTemporada(){
        
        $query = "SELECT 
                    ID_TEMPORADA,
                    NOMBRE,
                    INICIO,
                    FIN,
                    INICIO1,
                    FIN1
                FROM  PSTO_PLLA_TEMPORADA 
                WHERE ESTADO='1'
                ORDER BY ID_TEMPORADA";
      
        $oQuery = DB::select($query);        
        return $oQuery;

    }
    public static function listCondicionLaboral($id_cargo_proceso){
        
        if($id_cargo_proceso>0){
            $query = "SELECT 
                    ID_COND_LAB,
                    NOMBRE
                FROM  APS_CONDICION_LABORAL 
                WHERE ESTADO='1'
                AND ID_COND_LAB IN(
                    SELECT ID_COND_LAB FROM PSTO_PLLA_CARGO_PROCESO_CONLAB
                    WHERE ID_CARGO_PROCESO=".$id_cargo_proceso." 
                )
                ORDER BY NOMBRE";
        }else{
            $query = "SELECT 
                    ID_COND_LAB,
                    NOMBRE
                FROM  APS_CONDICION_LABORAL 
                WHERE ESTADO='1'
                ORDER BY NOMBRE";
        }
        
       //dd($query);
        $oQuery = DB::select($query);        
        return $oQuery;

    }
    public static function listProfesion(){
        
        $query = "SELECT 
                    ID_PROFESION,
                    NOMBRE
                FROM  APS_PROFESION 
                WHERE ESTADO='1'
                ORDER BY NOMBRE";
      
        $oQuery = DB::select($query);        
        return $oQuery;

    }
    public static function listTipoContrato($id_cargo_proceso){
        
        if($id_cargo_proceso>0){
            $query = "SELECT 
                    ID_TIPOCONTRATO,
                    NOMBRE,
                    NOMBRE_CORTO
                FROM  TIPO_CONTRATO 
                WHERE ID_TIPOCONTRATO IN(
                    SELECT ID_TIPOCONTRATO FROM PSTO_PLLA_CARGO_PROCESO_TIPCON
                    WHERE ID_CARGO_PROCESO=".$id_cargo_proceso." 
                )
                ORDER BY NOMBRE";
        }else{
            $query = "SELECT 
                    ID_TIPOCONTRATO,
                    NOMBRE,
                    NOMBRE_CORTO
                FROM  TIPO_CONTRATO 
                ORDER BY NOMBRE";
        }
        
        
        $oQuery = DB::select($query);        
        return $oQuery;

    }
    public static function listColumnas($dato){
        
        $query ="SELECT 
                    table_name, 
                    column_name
                FROM USER_TAB_COLUMNS
                WHERE table_name = 'PSTO_PLLA_PLANILLA'
                and lower(column_name) like lower('".$dato."%')
                order by column_name";
        
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listConceptoaps(){
        
        $query ="SELECT 
                    ID_CONCEPTOAPS, 
                    NOMBRE,
                    ID_TIPOCONCEPTOAPS,
                    COD_SUNAT
                FROM APS_CONCEPTO_PLANILLA
                WHERE ID_TIPOCONCEPTOAPS IN(100,11,13,300,10,12)
                order by NOMBRE";
        
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listCargo(){
        
        $query ="SELECT 
                    ID_CARGO, 
                    NOMBRE
                FROM APS_CARGO
                WHERE ESTADO='1'
                order by NOMBRE";
        
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listCondicionEscala(){
        
        $query ="SELECT 
                    ID_CONDICION_ESCALA, 
                    NOMBRE
                FROM PSTO_PLLA_CONDICION_ESCALA
                WHERE ESTADO='1'
                order by NOMBRE";
        
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showCargo($id){
        
        $query ="SELECT 
                    ID_CARGO, 
                    NOMBRE
                FROM APS_CARGO
                WHERE ID_CARGO=".$id."
                order by NOMBRE";
        
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listTipoEstatus(){
        
        $query ="SELECT 
                    ID_TIPOESTATUS, 
                    NOMBRE,
                    SIGLAS
                FROM TIPO_ESTATUS
                order by ID_TIPOESTATUS";
        
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listTipoPais(){
        
        $query ="SELECT 
                    ID_TIPOPAIS, 
                    NOMBRE,
                    ISO_A2,
                    ISO_A3
                FROM TIPO_PAIS
                order by NOMBRE";
        
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listDepatamentoArea($id_entidad,$id_area){
        
        $query ="SELECT 
                    ID_DEPTO, 
                    departamento as NOMBRE,
                    AREA
                FROM VW_AREA_DEPTO
                WHERE ID_ENTIDAD=".$id_entidad." 
                AND id_area_padre=".$id_area." 
                order by NOMBRE" ;
        
        $oQuery = DB::select($query);        
        return $oQuery;
    }
}
?>














