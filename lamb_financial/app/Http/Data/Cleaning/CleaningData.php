<?php
namespace App\Http\Data\Cleaning;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\cw\CWData;
use PDO;
class CleaningData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    
    public static function listGroup($id_entidad,$id_depto){
        
        $query = DB::table("serv_grupo as a");
        $query->leftjoin("moises.persona as p",'p.id_persona','=','a.id_persona');
        $query->leftjoin("moises.persona as pp",'pp.id_persona','=','a.id_adjunto');
        $query->select(
                'a.ID_GRUPO',
                'a.ID_ENTIDAD',
                'a.ID_DEPTO',
                'a.NOMBRE' ,
                'a.DESCRIPCION',
                'a.ID_PERSONA',
                'a.ID_ADJUNTO',
                'a.ESTADO',
                DB::raw("P.PATERNO||' '||P.MATERNO||' '||P.NOMBRE AS RESPONSABLE,
                 PP.PATERNO||' '||PP.MATERNO||' '||PP.NOMBRE AS ADJUNTO,
                (SELECT COUNT(*) FROM SERV_GRUPO_INTEGRANTES i where i.id_grupo = a.id_grupo) as cantidad
                ")
        );
        $query->where("a.ID_ENTIDAD",$id_entidad);
        $query->where("a.ID_DEPTO",$id_depto);
        
        $data= $query->paginate(20); 
        
       /* $sql = "SELECT 
                a.ID_GRUPO,
                a.ID_ENTIDAD,
                a.ID_DEPTO,
                a.NOMBRE,
                a.DESCRIPCION,
                a.ID_PERSONA,
                a.ID_ADJUNTO,
                a.ESTADO,
                P.PATERNO||' '||P.MATERNO||' '||P.NOMBRE AS RESPONSABLE,
                PP.PATERNO||' '||PP.MATERNO||' '||PP.NOMBRE AS ADJUNTO,
                (SELECT COUNT(*) FROM SERV_GRUPO_INTEGRANTES i where i.id_grupo=a.id_grupo) as cantidad
            FROM SERV_GRUPO  a LEFT JOIN MOISES.PERSONA P
            ON A.ID_PERSONA=P.ID_PERSONA
            LEFT JOIN MOISES.PERSONA PP
            ON  A.ID_ADJUNTO=PP.ID_PERSONA
            WHERE a.ID_ENTIDAD=".$id_entidad." 
            AND a.ID_DEPTO='".$id_depto."'
            ORDER BY  a.NOMBRE";
        $query = DB::select($sql);*/
        
        return $data;
    }
    public static function showGroup($id_grupo){
        $sql = "SELECT 
                a.ID_GRUPO,
                a.ID_ENTIDAD,
                a.ID_DEPTO,
                a.NOMBRE,
                a.DESCRIPCION,
                a.ID_PERSONA,
                a.ID_ADJUNTO,
                a.ESTADO
            FROM SERV_GRUPO  a
            WHERE a.id_grupo=".$id_grupo;
        $query = DB::select($sql);
        
        return $query;
    }
    public static function addGroup($id_entidad,$nombre,$descripcion,$id_persona,$id_adjunto,$id_depto,$estado){    
        
        $query = "SELECT 
                        COALESCE(MAX(ID_GRUPO),0)+1 ID_GRUPO
                FROM SERV_GRUPO ";  
        $oQuery = DB::select($query);
        $id_grupo = 0;
        foreach ($oQuery as $key => $item){
            $id_grupo = $item->id_grupo;                
        }
        if($id_grupo==0){
            $id_grupo = 1;
        }
        
        $data= DB::table('SERV_GRUPO')->insert(
            array('ID_GRUPO'=>$id_grupo,
                'ID_ENTIDAD' => $id_entidad,
                'ID_DEPTO' => $id_depto,
                'NOMBRE'=> $nombre,                
                'DESCRIPCION'=> $descripcion,
                'ID_PERSONA'=> $id_persona,
                'ID_ADJUNTO'=> $id_adjunto,
                'ESTADO'=> $estado
                )
        );   
        
        $query = "SELECT 
                    ID_GRUPO,
                    ID_ENTIDAD,
                    ID_DEPTO,
                    NOMBRE,
                    DESCRIPCION,
                    ID_PERSONA,
                    ID_ADJUNTO,
                    ESTADO
            FROM SERV_GRUPO
            WHERE ID_GRUPO=".$id_grupo."";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function updateGroup($id_grupo,$nombre,$descripcion,$id_persona,$id_adjunto,$estado){                 
        
        $query = "UPDATE SERV_GRUPO SET 
                    NOMBRE = '".$nombre."',
                    DESCRIPCION = '".$descripcion."',
                    ID_PERSONA = '".$id_persona."',
                    ID_ADJUNTO = '".$id_adjunto."',
                    ESTADO = '".$estado."'
              WHERE ID_GRUPO = ".$id_grupo;
        DB::update($query);
        
        $query = "SELECT 
                    ID_GRUPO,
                    ID_ENTIDAD,
                    ID_DEPTO,
                    NOMBRE,
                    DESCRIPCION,
                    ID_PERSONA,
                    ID_ADJUNTO,
                    ESTADO
            FROM SERV_GRUPO
            WHERE ID_GRUPO=".$id_grupo."";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function deleteGroup($id_grupo){ 
        $query = "DELETE FROM SERV_GRUPO 
                WHERE ID_GRUPO = ".$id_grupo;
        DB::delete($query);
    }
    public static function listPersona($id_entidad,$id_depto){
        
        $sql = "SELECT 
                P.ID_PERSONA,
                P.PATERNO,
                P.MATERNO,
                P.NOMBRE
            FROM MOISES.PERSONA P , APS_TRABAJADOR T
            WHERE P.ID_PERSONA =T.ID_PERSONA 
            AND T.ID_ENTIDAD=".$id_entidad." 
            AND T.ID_DEPTO='".$id_depto."'
            AND T.ID_PERSONA IN(
                SELECT x.ID_PERSONA FROM APS_EMPLEADO  x
                WHERE X.ID_ENTIDAD=".$id_entidad."
                AND x.ID_DEPTO='".$id_depto."'
                AND x.estado='A'
            )
            union
            SELECT 
                P.ID_PERSONA,
                P.PATERNO,
                P.MATERNO,
                P.NOMBRE
            FROM MOISES.PERSONA P , APS_TRABAJADOR T
            WHERE P.ID_PERSONA =T.ID_PERSONA
            AND P.ID_PERSONA=20145
            ORDER BY  PATERNO, MATERNO,  NOMBRE";
        $query = DB::select($sql);
          
        return $query;
    }
    public static function listGrupoIntegrantes($id_grupo){
        
        $sql = "SELECT 
                P.ID_PERSONA,
                P.PATERNO,
                P.MATERNO,
                P.NOMBRE,
                A.ID_GRUPO_INTEGRANTES,
                A.ID_GRUPO,
                A.ID_PERSONA,
                A.HENTRADA,
                A.HSALIDA,
                A.ESTADO
            FROM SERV_GRUPO_INTEGRANTES A, MOISES.PERSONA P
            WHERE A.ID_PERSONA=P.ID_PERSONA
            AND A.ID_GRUPO=".$id_grupo."
            ORDER BY  p.PATERNO, p.MATERNO,  p.NOMBRE";
        $query = DB::select($sql);
          
        return $query;
    }
    
    public static function addGrupoIntegrantes($id_grupo,$detail){    
      

        foreach($detail as $items){
            $id_persona  = $items->id_persona;
            $hentrada    = $items->hentrada;
            $hsalida     = $items->hsalida;
            $estado      = '1';
            
            $query = "SELECT 
                    ID_GRUPO_INTEGRANTES
                FROM SERV_GRUPO_INTEGRANTES
                WHERE ID_PERSONA=".$id_persona." 
                AND ID_GRUPO=".$id_grupo."";
            $oQuery = DB::select($query);
            
            if(count($oQuery)==0){
            
                $query = "SELECT 
                                COALESCE(MAX(ID_GRUPO_INTEGRANTES),0)+1 ID_GRUPO_INTEGRANTES
                        FROM SERV_GRUPO_INTEGRANTES ";  
                $oQuery = DB::select($query);
                $id_grupo_integrante = 0;
                foreach ($oQuery as $key => $item){
                    $id_grupo_integrante = $item->id_grupo_integrantes;                
                }
                if($id_grupo_integrante==0){
                    $id_grupo_integrante = 1;
                }

                $data= DB::table('SERV_GRUPO_INTEGRANTES')->insert(
                    array('ID_GRUPO_INTEGRANTES'=>$id_grupo_integrante,
                        'ID_GRUPO' => $id_grupo,
                        'ID_PERSONA' => $id_persona,
                        'HENTRADA'=> $hentrada,                
                        'HSALIDA'=> $hsalida,
                        'ESTADO'=> $estado
                        )
                );  
            }
        
        }
    }
    public static function updateGrupoIntegrantes($id_grupo_integrante,$id_persona,$hentrada,$hsalida,$estado){                 
        
        $query = "UPDATE SERV_GRUPO_INTEGRANTES SET 
                    ID_PERSONA = '".$id_persona."',
                    HENTRADA = '".$hentrada."',
                    HSALIDA = '".$hsalida."',
                    ESTADO ='".$estado."'
              WHERE ID_GRUPO_INTEGRANTES = ".$id_grupo_integrante;
        DB::update($query);
        
        $query = "SELECT 
                    ID_GRUPO_INTEGRANTES,
                    ID_GRUPO,
                    ID_PERSONA,
                    HENTRADA,
                    HSALIDA,
                    ESTADO
            FROM SERV_GRUPO_INTEGRANTES
            WHERE ID_GRUPO_INTEGRANTES=".$id_grupo_integrante."";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showGrupoIntegrantes($id_grupo_integrante){                 
        
        
        $query = "SELECT 
                    ID_GRUPO_INTEGRANTES,
                    ID_GRUPO,
                    ID_PERSONA,
                    HENTRADA,
                    HSALIDA,
                    ESTADO
            FROM SERV_GRUPO_INTEGRANTES
            WHERE ID_GRUPO_INTEGRANTES=".$id_grupo_integrante."";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function deleteGrupoIntegrantes($id_grupo_integrante){ 
        $query = "DELETE FROM SERV_GRUPO_INTEGRANTES 
                WHERE ID_GRUPO_INTEGRANTES = ".$id_grupo_integrante;
        DB::delete($query);
    }
    public static function listGrupoServicio($id_grupo){
        
        $sql = "SELECT 
                P.ID_PERSONA,
                P.PATERNO,
                P.MATERNO,
                P.NOMBRE,
                A.ID_GRUPO_SERVICIOS,
                A.ID_GRUPO,
                A.DESCRIPCION,
                A.COMENTARIO,
                A.ID_PERSONA
            FROM SERV_GRUPO_SERVICIOS A LEFT JOIN MOISES.PERSONA P
            ON A.ID_PERSONA=P.ID_PERSONA  
            WHERE A.ID_GRUPO=".$id_grupo."
            ORDER BY  A.DESCRIPCION";
        $query = DB::select($sql);
          
        return $query;
    }
    
    public static function addGrupoServicio($id_grupo,$detail){    
        

        foreach($detail as $items){
            $id_persona  = $items->id_persona;
            $descripcion    = $items->descripcion;
            $comentario    = $items->comentario;

            
            $query = "SELECT 
                            COALESCE(MAX(ID_GRUPO_SERVICIOS),0)+1 ID_GRUPO_SERVICIO
                    FROM SERV_GRUPO_SERVICIOS ";  
            $oQuery = DB::select($query);
            $id_grupo_servicio = 0;
            foreach ($oQuery as $key => $item){
                $id_grupo_servicio = $item->id_grupo_servicio;                
            }
            if($id_grupo_servicio==0){
                $id_grupo_servicio = 1;
            }

            $data= DB::table('SERV_GRUPO_SERVICIOS')->insert(
                array('ID_GRUPO_SERVICIOS'=>$id_grupo_servicio,
                    'ID_GRUPO' => $id_grupo,
                    'ID_PERSONA' => $id_persona,
                    'DESCRIPCION'=> $descripcion,                
                    'COMENTARIO'=> $comentario
                    )
            );   
        
        }
    }
    public static function updateGrupoServicio($id_grupo_servicio,$id_persona,$descripcion,$comentario){                 
        
        $query = "UPDATE SERV_GRUPO_SERVICIOS SET 
                    DESCRIPCION = '".$descripcion."',
                    ID_PERSONA = '".$id_persona."',
                    COMENTARIO = '".$comentario."'
              WHERE ID_GRUPO_SERVICIOS = ".$id_grupo_servicio;
        DB::update($query);
        
        $query = "SELECT 
                    ID_GRUPO_SERVICIOS,
                    ID_GRUPO,
                    DESCRIPCION,
                    COMENTARIO,
                    ID_PERSONA
            FROM SERV_GRUPO_SERVICIOS
            WHERE ID_GRUPO_SERVICIOS=".$id_grupo_servicio."";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function showGrupoServicio($id_grupo_servicio){                 
        
        $query = "SELECT 
                    ID_GRUPO_SERVICIOS,
                    ID_GRUPO,
                    DESCRIPCION,
                    COMENTARIO,
                    ID_PERSONA
            FROM SERV_GRUPO_SERVICIOS
            WHERE ID_GRUPO_SERVICIOS=".$id_grupo_servicio."";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function deleteGrupoServicio($id_grupo_servicio){ 
        $query = "DELETE FROM SERV_GRUPO_SERVICIOS 
                WHERE ID_GRUPO_SERVICIOS = ".$id_grupo_servicio;
        DB::delete($query);
    }
    
    //control
    public static function asistencia($id_grupo, $id_user){
        
        //$sql="select to_char(sysdate,'DD-MM-YYYY D HH24:MI') from dual";
        $sql="select 
            to_char(sysdate,'D') as dias,
            to_char(sysdate,'HH24') as horas 
            from dual";
        $dias=0;
        $horas = 0;
        $data = DB::select($sql);
        foreach($data as $row){
            $dias  = $row->dias;
            $horas = $row->horas;
        }
        $id_entidad  = 0;
        $sql="select id_entidad from serv_grupo  where id_grupo=".$id_grupo;
        $data = DB::select($sql);
        foreach($data as $row){
            $id_entidad  = $row->id_entidad;
        }
        $pdo = DB::getPdo();
        
        $stmt = $pdo->prepare("begin PKG_HUMAN_TALENT.SP_GENERA_CONTROL_CULTO_LIMP(
                                        :ID_ENTIDAD
                                     ); end;");
        $stmt->bindParam(':ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
    
        $stmt->execute(); 
        
        $return = ['nerror'=>0];
        
        //if (($dias>=3 and $dias<=5) and ($horas>=7 and $horas<=8)){
            
        CleaningData::actualizarMarcacion($id_entidad,$id_grupo,$id_user);
            
            $sql="SELECT 
                P.ID_CONTROL,
                D.NOMBRE,
                D.PATERNO,
                D.MATERNO,
                P.ASISTENCIA,
                P.ASISTENCIA_CULTO,
                P.FECHA_MARCACION,
                FC_DOCUMENTO_CLIENTE(D.ID_PERSONA) DNI
            FROM SERV_CONTROL P, MOISES.PERSONA D
            WHERE P.ID_PERSONA = D.ID_PERSONA
             AND P.ID_GRUPO=".$id_grupo."
            AND TO_CHAR(P.FECHA,'DDMMYYYY') = TO_CHAR(SYSDATE,'DDMMYYYY') 
            ORDER BY D.PATERNO,D.MATERNO,D.NOMBRE";
        
            $data = DB::select($sql);
            
            $return = ['nerror'=>0,'mensaje'=>'','data'=>$data];
            
        /*}else{
            $return = ['nerror'=>1,'mensaje'=>'Solo disponible de 7 a 8 am','data'=>[]];
        }*/
        return $return;
    }
    
    public static function actualizarMarcacion($id_entidad,$id_grupo,$id_persona){
        
        $sql="SELECT 
                P.ID_CONTROL,
                D.NUM_DOCUMENTO 
            FROM SERV_CONTROL P, MOISES.PERSONA_DOCUMENTO D
            WHERE P.ID_PERSONA = D.ID_PERSONA
            AND D.ID_TIPODOCUMENTO IN(1,4)
            AND P.ID_GRUPO=".$id_grupo."
            AND TO_CHAR(P.FECHA,'DDMMYYYY')=TO_CHAR(SYSDATE,'DDMMYYYY') ";
        
        $data = DB::select($sql);
        foreach($data as $row){
            
            $dataasist = CWData::showAsistenciaTrabajador($row->num_documento);
            
            if(count($dataasist)>0){
                $fecmar = "";
                foreach($dataasist as $rows){
                    $fecmar = $rows->fechahora;
                }
                $query = "UPDATE SERV_CONTROL SET 
                                ASISTENCIA ='1',
                                ASISTENCIA_CULTO ='1',
                                FECREGISTRADO=SYSDATE,
                                FECHA_MARCACION='".$fecmar."',
                                ID_PERSONA_REG=".$id_persona." 
                    WHERE ID_CONTROL = ".$row->id_control."
                    AND ACCION = '0' ";
                DB::update($query);
            }
            
          
        }
    
    }
    public static function actualizarControl($id_control,$asistencia,$id_persona){
        
        $query = "UPDATE SERV_CONTROL SET 
                ASISTENCIA_CULTO ='".$asistencia."',
                FECREGISTRADO=SYSDATE,
                ID_PERSONA_REG=".$id_persona." 
            WHERE ID_CONTROL = ".$id_control;
        
        DB::update($query);
                
    }
    public static function listarGrupoUser($id_persona){
        $query = "SELECT 
                    ID_GRUPO,
                    ID_ENTIDAD,
                    ID_DEPTO,
                    NOMBRE,
                    DESCRIPCION,
                    ID_PERSONA,
                    ID_ADJUNTO,
                    ESTADO
            FROM SERV_GRUPO
            WHERE (ID_PERSONA=".$id_persona." or ID_ADJUNTO=".$id_persona.") 
            ORDER BY ID_GRUPO";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function listPersonal($id_entidad,$id_grupo){                
        $query = "SELECT 
                    A.ID_PERSONA,
                    B.ID_ENTIDAD,
                    C.ID_GRUPO,
                    A.PATERNO||' '||A.MATERNO||' '||A.NOMBRE AS NOMBRES,
                    B.LETRA,
                    B.NUMERO,
                    B.CONFIANZA
                    FROM MOISES.PERSONA A, APS_TRABAJADOR B, SERV_GRUPO_INTEGRANTES C
                    WHERE A.ID_PERSONA = B.ID_PERSONA
                    AND A.ID_PERSONA=c.ID_PERSONA
                    AND b.ID_PERSONA=c.ID_PERSONA
                    AND B.ID_ENTIDAD = ".$id_entidad."      
                    AND C.ID_GRUPO = '".$id_grupo."'
                    ORDER BY NOMBRES ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showFecha($fecha){                
        $query = "SELECT TO_CHAR(TO_DATE('".$fecha."')+7,'DDMMYYYY') AS FECHA FROM DUAL ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function showSemana($fecha){
        $fec =$fecha;//substr($fecha,6,4).'-'.substr($fecha,3,2).'-'.substr($fecha,0,2) ;
        $query = "SELECT 
                TO_CHAR(TO_DATE(TO_DATE('".$fec."')+CODIGO-1),'DD/MM/YYYY') AS FECHA,
                DECODE(RTRIM(LTRIM(TO_CHAR(TO_DATE(TO_DATE('".$fec."')+CODIGO-1), 'DAY', 'NLS_DATE_LANGUAGE=ENGLISH'))),'SUNDAY',1,'MONDAY',2, 'TUESDAY',3, 'WEDNESDAY',4, 'THURSDAY',5,'FRIDAY',6, 'SATURDAY',7) NDIA,
                DECODE(RTRIM(LTRIM(TO_CHAR(TO_DATE(TO_DATE('".$fec."')+CODIGO-1), 'DAY', 'NLS_DATE_LANGUAGE=ENGLISH'))),'SUNDAY','Domingo','MONDAY','Lunes', 'TUESDAY','Martes', 'WEDNESDAY','Miercoles', 'THURSDAY','Jueves','FRIDAY','Viernes', 'SATURDAY','Sábado') DIAS
                FROM EVAL_PERIODO_DETALLE
                WHERE ID_PERIODO = 1
                order by CODIGO";
        //dd($query);
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listPersonalAssists($id_persona,$id_grupo,$fecha,$fecha_a){  
        
        $fec =substr($fecha,6,4).'-'.substr($fecha,3,2).'-'.substr($fecha,0,2) ;
        $sql="select to_char(to_date('".$fec."'),'D') as dia from dual";
        $data = DB::select($sql);
        $dia=0;
        foreach($data as $row) {
             $dia=$row->dia;
        }
        $total=7;
        $resta = $total-($total-$dia)-1;
        //dd($resta);
        
        $data=[];
        $r = 1;
   
        for($h=1;$h<=7;$h++){
            
            $query = "SELECT 
                    P.ID_CONTROL,
                    P.ASISTENCIA,
                    P.ASISTENCIA_CULTO,
                    P.FECHA,
                    p.FECHA_MARCACION,
                    DECODE(RTRIM(LTRIM(TO_CHAR(P.FECHA, 'DAY', 'NLS_DATE_LANGUAGE=ENGLISH'))),'SUNDAY',1,'MONDAY',2, 'TUESDAY',3, 'WEDNESDAY',4, 'THURSDAY',5,'FRIDAY',6, 'SATURDAY',7) NDIA
                FROM SERV_CONTROL P
                WHERE P.ID_PERSONA = ".$id_persona."
                AND P.ID_GRUPO = ".$id_grupo."
                AND TO_CHAR(P.FECHA,'YYYYMMDD') = '".str_replace('-', '', $fec)."'
                ORDER BY P.FECHA ";
            $oQuery = DB::select($query);
            
            if(count($oQuery)>0){
                foreach($oQuery as $row){
                    $reg=array();
                    $reg['id_control'] = $row->id_control;
                    $reg['asistencia'] = $row->asistencia;
                    $reg['asistencia_culto'] = $row->asistencia_culto;
                    $reg['fecha'] = $row->fecha;
                    $reg['fecha_marcacion'] = $row->fecha_marcacion;
                    $reg['ndia'] = $row->ndia;
                    $reg['accion'] = 1;
                    $data[]=$reg;
                }
            }else{
                $reg=array();
                $reg['id_control'] = 0;
                $reg['asistencia'] = 0;
                $reg['asistencia_culto'] = 0;
                $reg['fecha'] = '';
                $reg['fecha_marcacion'] = '';
                $reg['ndia'] = $h;
                $reg['accion'] = 0;
                $data[]=$reg;
            }
            $fec = date ("Y-m-d", strtotime("+1 day", strtotime($fec)));
           
        }
        
       
        return $data;
    }
    public static function listGroupActivo($id_entidad,$id_depto){
        
        
        
        $sql = "SELECT 
                a.ID_GRUPO,
                a.ID_ENTIDAD,
                a.ID_DEPTO,
                a.NOMBRE,
                a.DESCRIPCION,
                a.ID_PERSONA,
                a.ID_ADJUNTO,
                a.ESTADO,
                P.PATERNO||' '||P.MATERNO||' '||P.NOMBRE AS RESPONSABLE,
                PP.PATERNO||' '||PP.MATERNO||' '||PP.NOMBRE AS ADJUNTO
            FROM SERV_GRUPO  a LEFT JOIN MOISES.PERSONA P
            ON A.ID_PERSONA=P.ID_PERSONA
            LEFT JOIN MOISES.PERSONA PP
            ON  A.ID_ADJUNTO=PP.ID_PERSONA
            WHERE a.ID_ENTIDAD=".$id_entidad." 
            AND a.ID_DEPTO='".$id_depto."' 
            ORDER BY  a.NOMBRE";
        $data = DB::select($sql);
        
        return $data;
    }
    
}

