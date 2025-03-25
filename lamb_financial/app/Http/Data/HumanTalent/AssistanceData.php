<?php
namespace App\Http\Data\HumanTalent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\cw\CWData;
use PDO;
class AssistanceData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    
     public static function asistencia($id_entidad,$id_depto,$id_persona ){
        
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
        
        $pdo = DB::getPdo();
        
        $stmt = $pdo->prepare("begin PKG_HUMAN_TALENT.SP_GENERA_CONTROL_CULTO(
                                        :ID_ENTIDAD
                                     ); end;");
        $stmt->bindParam(':ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
    
        $stmt->execute(); 
        
        $return = ['nerror'=>0];
        
        if (($dias>=2 and $dias<=6) and ($horas>=6 and $horas<=20)){
            
            AssistanceData::actualizarMarcacion($id_entidad,$id_depto,$id_persona);
            if($id_depto == "0"){
                $depto = "AND P.ID_DEPTO IN ( SELECT ID_DEPTO 
                            FROM LAMB_USERS_DEPTO
                            WHERE ID = ".$id_persona."
                            AND ID_ENTIDAD = ".$id_entidad." )";
            }else{
                $depto = "AND P.ID_DEPTO = '".$id_depto."' ";
            }
            
            $sql="SELECT 
                P.ID_CONTROL_CULTO,
                D.NOMBRE,
                D.PATERNO,
                D.MATERNO,
                P.ASISTENCIA,
                P.ASISTENCIA_CULTO,
                (CASE WHEN P.ASISTENCIA_CULTO = 1 THEN 'true' ELSE 'false' END) as ASISTENCIA_CULTO_BOOL,
                P.FECHA_MARCACION,
                FC_DOCUMENTO_CLIENTE(D.ID_PERSONA) DNI ,
                D.ID_PERSONA
            FROM APS_CONTROL_CULTO P, MOISES.VW_PERSONA_NATURAL_TRABAJADOR D
            WHERE P.ID_PERSONA = D.ID_PERSONA
            AND P.ID_ENTIDAD=".$id_entidad." 
            ".$depto." 
            AND D.ESTADO = 'A'
            AND TO_CHAR(P.FEC_FECHA,'DDMMYYYY') = TO_CHAR(SYSDATE,'DDMMYYYY') 
            ORDER BY D.PATERNO,D.MATERNO,D.NOMBRE ";
        
            $data = DB::select($sql);
            
            $return = ['nerror'=>0,'mensaje'=>'','data'=>$data];
            
        }else{
            $return = ['nerror'=>1,'mensaje'=>'Solo disponible de 7 a 8 am','data'=>[]];
        }
        return $return;
    }
    
    public static function actualizarMarcacion($id_entidad,$id_depto,$id_persona){
        if($id_depto == "0"){
            $depto = "AND P.ID_DEPTO IN ( SELECT ID_DEPTO 
                        FROM LAMB_USERS_DEPTO
                        WHERE ID = ".$id_persona."
                        AND ID_ENTIDAD = ".$id_entidad." )";
        }else{
            $depto = "AND P.ID_DEPTO = '".$id_depto."' ";
        }
        $sql="SELECT 
                P.ID_CONTROL_CULTO,
                D.NUM_DOCUMENTO 
            FROM APS_CONTROL_CULTO P, MOISES.PERSONA_DOCUMENTO D
            WHERE P.ID_PERSONA = D.ID_PERSONA
            AND D.ID_TIPODOCUMENTO IN(1,4)
            AND P.ID_ENTIDAD=".$id_entidad." 
            ".$depto." 
            AND TO_CHAR(P.FEC_FECHA,'DDMMYYYY')=TO_CHAR(SYSDATE,'DDMMYYYY') ";
        
        $data = DB::select($sql);
        foreach($data as $row){
            $dataasist = CWData::showAsistenciaTrabajador($row->num_documento);
            
            if(count($dataasist)>0){
                $fecmar = "";
                foreach($dataasist as $rows){
                    $fecmar = $rows->fechahora;
                }
                $query = "UPDATE APS_CONTROL_CULTO SET 
                                ASISTENCIA ='1',
                                ASISTENCIA_CULTO ='1',
                                FECREGISTRADO=SYSDATE,
                                FECHA_MARCACION='".$fecmar."',
                                ID_PERSONA_REG=".$id_persona." 
                    WHERE ID_CONTROL_CULTO = ".$row->id_control_culto."
                    AND ACCION = '0' ";
                DB::update($query);
            }
        }
    
    }
    public static function actualizarControl($id_control_culto,$asistencia,$id_persona){
        
        $query = "UPDATE APS_CONTROL_CULTO SET 
                        ASISTENCIA_CULTO ='".$asistencia."',
                        FECREGISTRADO=SYSDATE,
                        ID_PERSONA_REG=".$id_persona." 
                WHERE ID_CONTROL_CULTO = ".$id_control_culto;
        
        DB::update($query);
                
    }
    public static function listPersonal($id_entidad,$id_depto,$id_user){    
        if($id_depto == "0"){
            $depto = "AND B.ID_DEPTO IN ( SELECT ID_DEPTO 
                        FROM LAMB_USERS_DEPTO
                        WHERE ID = ".$id_user."
                        AND ID_ENTIDAD = ".$id_entidad." )";
        }else{
            $depto = "AND B.ID_DEPTO = '".$id_depto."' ";
        }
        $query = "SELECT 
                    A.ID_PERSONA,B.ID_ENTIDAD,B.ID_DEPTO,A.PATERNO||' '||A.MATERNO||' '||A.NOMBRE AS NOMBRES,B.LETRA,B.NUMERO,B.CONFIANZA,FC_DOCUMENTO_CLIENTE(A.ID_PERSONA) DNI
                    FROM MOISES.PERSONA A, APS_TRABAJADOR B
                    WHERE A.ID_PERSONA = B.ID_PERSONA
                    AND B.ID_ENTIDAD = ".$id_entidad."      
                    ".$depto."
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
    public static function listPersonalAssists($id_persona,$fecha,$fecha_a){  
        
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
        /*for($r=1;$r<=$resta;$r++){
            $reg=array();
            $reg['id_control_culto'] = 0;
            $reg['asistencia'] = 0;
            $reg['asistencia_culto'] = 0;
            $reg['fec_fecha'] = '';
            $reg['fecha_marcacion'] = '';
            $reg['ndia'] = $r;
            $reg['accion'] = 0;
            $data[]=$reg;
        }*/
        
        //25-05-2017
        
       
       //date('Y-m-d',strtotime($fecha)));
        for($h=1;$h<=7;$h++){
            
            $query = "SELECT 
                    P.ID_CONTROL_CULTO,
                    P.ASISTENCIA,
                    P.ASISTENCIA_CULTO,
                    P.FEC_FECHA,
                    p.FECHA_MARCACION,
                    DECODE(RTRIM(LTRIM(TO_CHAR(P.FEC_FECHA, 'DAY', 'NLS_DATE_LANGUAGE=ENGLISH'))),'SUNDAY',1,'MONDAY',2, 'TUESDAY',3, 'WEDNESDAY',4, 'THURSDAY',5,'FRIDAY',6, 'SATURDAY',7) NDIA
                FROM APS_CONTROL_CULTO P
                WHERE P.ID_PERSONA = ".$id_persona."
                AND TO_CHAR(P.FEC_FECHA,'YYYYMMDD') = '".str_replace('-', '', $fec)."'
                ORDER BY P.FEC_FECHA ";
            $oQuery = DB::select($query);
            
            if(count($oQuery)>0){
                foreach($oQuery as $row){
                    $reg=array();
                    $reg['id_control_culto'] = $row->id_control_culto;
                    $reg['asistencia'] = $row->asistencia;
                    $reg['asistencia_culto'] = $row->asistencia_culto;
                    $reg['fec_fecha'] = $row->fec_fecha;
                    $reg['fecha_marcacion'] = $row->fecha_marcacion;
                    $reg['ndia'] = $row->ndia;
                    $reg['accion'] = 1;
                    $data[]=$reg;
                }
            }else{
                $reg=array();
                $reg['id_control_culto'] = 0;
                $reg['asistencia'] = 0;
                $reg['asistencia_culto'] = 0;
                $reg['fec_fecha'] = '';
                $reg['fecha_marcacion'] = '';
                $reg['ndia'] = $h;
                $reg['accion'] = 0;
                $data[]=$reg;
            }
            $fec = date ("Y-m-d", strtotime("+1 day", strtotime($fec)));
           
        }    
        
        
             
        
        /*$query = "SELECT 
                    P.ID_CONTROL_CULTO,
                    P.ASISTENCIA,
                    P.ASISTENCIA_CULTO,
                    P.FEC_FECHA,
                    DECODE(RTRIM(LTRIM(TO_CHAR(P.FEC_FECHA, 'DAY', 'NLS_DATE_LANGUAGE=ENGLISH'))),'SUNDAY',1,'MONDAY',2, 'TUESDAY',3, 'WEDNESDAY',4, 'THURSDAY',5,'FRIDAY',6, 'SATURDAY',7) NDIA
                FROM APS_CONTROL_CULTO P
                WHERE P.ID_PERSONA = ".$id_persona."
                AND TO_CHAR(P.FEC_FECHA,'DDMMYYYY') BETWEEN '".$fecha."' AND '".$fecha_a."'
                ORDER BY P.FEC_FECHA ";
        $oQuery = DB::select($query);  */      
        return $data;
    }



    public static function polygonsUser($id_persona){

        // Poligonos del usuario, la parte comentada tambien funciona solo es en ORM (POLYGONS)

        // $polygons = DB::table('APS_MAPA_COORDENADA')
        // ->select(
        //     'APS_MAPA_COORDENADA.ID_MAPA',
        //     'APS_MAPA_COORDENADA.LAT',
        //     'APS_MAPA_COORDENADA.LNG',
        //     'APS_MAPA_POLIGONO.NOMBRE')
        // ->join('APS_MAPA_POLIGONO', 'APS_MAPA_COORDENADA.ID_MAPA', '=', 'APS_MAPA_POLIGONO.ID_MAPA')
        // ->whereIn('APS_MAPA_POLIGONO.ID_MAPA', function($query) use ($id_persona){

        //     $deptos = DB::table('APS_MAPA_DEPTO')
        //     ->select('APS_MAPA_DEPTO.ID_MAPA')
        //     ->whereIn('APS_MAPA_DEPTO.ID_DEPTO', function($q) use ($id_persona){
        //         $q->select('APS_TRABAJADOR.ID_DEPTO')
        //         ->from('APS_TRABAJADOR')
        //         ->where('APS_TRABAJADOR.ID_PERSONA','=',$id_persona);
        //     });


        //     $query->select('APS_MAPA_PERSONA.ID_MAPA')
        //     ->from('APS_MAPA_PERSONA')
        //     ->where('APS_MAPA_PERSONA.ID_PERSONA', '=', $id_persona)
        //     ->union($deptos);
        // })->get();
        // return $polygons;

        $q = "select mc.ID_MAPA, mc.LAT, mc.LNG, mp.NOMBRE from APS_MAPA_COORDENADA mc, APS_MAPA_POLIGONO mp
        where mc.ID_MAPA=mp.ID_MAPA
        and mp.ID_MAPA in (
        SELECT md.ID_MAPA FROM APS_MAPA_DEPTO md WHERE md.ID_DEPTO IN (
        SELECT t.ID_DEPTO from APS_TRABAJADOR t WHERE t.ID_PERSONA=".$id_persona.")
        union
        SELECT mp.ID_MAPA FROM APS_MAPA_PERSONA mp WHERE mp.ID_PERSONA=".$id_persona."
        )";
        $r = DB::select($q);
        return $r;

        
    }

    public static function testPolygonUser(){

        // TEST (POLYGONS)

        $sql = "UPDATE APS_MAPA_POLIGONO t SET NOMBRE='Colegio Union' WHERE ID_MAPA = '1'";
        $data = DB::update($sql);
        return [];

    }


    public static function searchPersonForPolygon($q, $id_mapa){


        // (POLYGONS)

        $exists = DB::table("APS_MAPA_PERSONA")
        ->select('APS_MAPA_PERSONA.ID_PERSONA')
        ->where('APS_MAPA_PERSONA.ID_MAPA','=',$id_mapa)
        ->get()
        ->map(function ($item, $key) {
            return $item->id_persona;
        });

        $q=$q."%";
        $query = DB::table("moises.vw_persona_natural")
        ->select("id_persona AS id",
            "nom_persona AS name",
            "num_documento AS description")
        ->whereRaw("lower(paterno) like lower('".$q."') or lower(materno) like lower('".$q."') or lower(nombre) like lower('".$q."')
            or lower(nombre||' '||paterno) like lower('".$q."') or lower(nombre||' '||paterno||' '||materno) like lower('".$q."')
            or lower(paterno||' '||materno) like lower('".$q."') or lower(paterno||' '||materno||' '||nombre) like lower('".$q."') or num_documento like '".$q."'")
        ->orderBy('paterno', 'asc')
        ->orderBy('materno', 'asc')
        ->orderBy('nombre', 'asc')
        ->get()
        ->whereNotIn('id', $exists);
        return collect($query)->unique('id')->values()->all();
    }

    public static function departmentsForPolygon($id_entidad, $q, $id_mapa){

        // (POLYGONS)

        $exists = DB::table("APS_MAPA_DEPTO")
        ->select('APS_MAPA_DEPTO.ID_DEPTO')
        ->where('APS_MAPA_DEPTO.ID_MAPA','=',$id_mapa)
        ->get()
        ->map(function ($item, $key) {
            return $item->id_depto;
        });

        $query = DB::table("CONTA_ENTIDAD_DEPTO")
        ->select(
            'CONTA_ENTIDAD_DEPTO.ID_DEPTO AS ID',
            'CONTA_ENTIDAD_DEPTO.NOMBRE AS NAME')
        ->where('CONTA_ENTIDAD_DEPTO.ID_ENTIDAD','=',$id_entidad)
        ->where('CONTA_ENTIDAD_DEPTO.ES_GRUPO','=',0)
        ->whereRaw("UPPER(CONTA_ENTIDAD_DEPTO.NOMBRE) LIKE UPPER('%".$q."%')")
        ->orderBy('CONTA_ENTIDAD_DEPTO.NOMBRE', 'asc')
        ->get()
        ->whereNotIn('id', $exists);

        return collect($query)->unique('id')->values()->all();
    }

    public static function polygonsByEntity($id_entidad, $id_depto){

        // (POLYGONS)

        $data = DB::table('APS_MAPA_COORDENADA')
        ->select(
            'APS_MAPA_COORDENADA.ID_COORDENADA AS ID',
            'APS_MAPA_COORDENADA.ID_MAPA',
            'APS_MAPA_POLIGONO.NOMBRE',
            'APS_MAPA_COORDENADA.LAT',
            'APS_MAPA_COORDENADA.LNG')
        ->leftJoin('APS_MAPA_POLIGONO', 'APS_MAPA_COORDENADA.ID_MAPA', '=', 'APS_MAPA_POLIGONO.ID_MAPA')
        ->where('APS_MAPA_POLIGONO.ID_ENTIDAD','=',$id_entidad)
        ->where('APS_MAPA_POLIGONO.ID_DEPTO','=',$id_depto)
        ->orderBy('APS_MAPA_COORDENADA.ORDEN', 'DESC')
        ->get();
        return $data;
    }



    public static function departmentsByEntity($id_entidad,$id_persona){

        // (POLYGONS)

        $query = "SELECT
            A.ID_DEPTO, A.NOMBRE
            FROM CONTA_ENTIDAD_DEPTO A, LAMB_USERS_DEPTO B
            WHERE A.ID_ENTIDAD = B.ID_ENTIDAD
            AND A.ID_DEPTO = B.ID_DEPTO
            AND B.ID_ENTIDAD = ".$id_entidad."
            AND B.ID = ".$id_persona."
            AND LENGTH(A.ID_DEPTO) = 1
            AND A.ES_GRUPO = '1'
            ORDER BY A.ID_DEPTO";
        return DB::select($query);
    }

    public static function idPolygonsExist($id_entidad, $id_depto){

        // (POLYGONS)

        $data = DB::table('APS_MAPA_COORDENADA')
        ->select('APS_MAPA_COORDENADA.ID_MAPA AS ID')
        ->leftJoin('APS_MAPA_POLIGONO', 'APS_MAPA_COORDENADA.ID_MAPA', '=', 'APS_MAPA_POLIGONO.ID_MAPA')
        ->where('APS_MAPA_POLIGONO.ID_ENTIDAD','=',$id_entidad)
        ->where('APS_MAPA_POLIGONO.ID_DEPTO','=',$id_depto)
        ->distinct()
        ->get();
        return $data->map(function ($obj) {
            return $obj->id;});
    }

    public static function insertCoordinates($data){
        
        // (POLYGONS)

        $q = DB::table('APS_MAPA_COORDENADA')->insert($data);
        return $q;
    }

    public static function deleteCoordinates($id_mapa){

        // (POLYGONS)

        DB::table('APS_MAPA_COORDENADA')
        ->where('APS_MAPA_COORDENADA.ID_MAPA','=',$id_mapa)
        ->delete();
    }

    public static function insertPolygon($data){

        // (POLYGONS)

        $state = DB::table('APS_MAPA_POLIGONO')->insert($data);
        if ($state) {
            $sequence = DB::getSequence();
            return $sequence->currentValue('SQ_APS_MAPA_POLIGONO_ID');
        }else {
            return null;
        }
        
    }

    public static function deletePolygon($id_mapa){

        // (POLYGONS)

        DB::table('APS_MAPA_POLIGONO')
        ->where('APS_MAPA_POLIGONO.ID_MAPA','=',$id_mapa)
        ->delete();        
    }


    public static function selectDeptosPolygon($id_mapa){

        // (POLYGONS)

        return DB::table('APS_MAPA_DEPTO')
        ->select(
            'CONTA_ENTIDAD_DEPTO.NOMBRE',
            'APS_MAPA_DEPTO.ID_ENTIDAD',
            'APS_MAPA_DEPTO.ID_DEPTO')
        ->join('CONTA_ENTIDAD_DEPTO','APS_MAPA_DEPTO.ID_DEPTO','=','CONTA_ENTIDAD_DEPTO.ID_DEPTO')
        ->where('APS_MAPA_DEPTO.ID_MAPA','=',$id_mapa)
        ->get()
        ->unique('id_depto')
        ->values()
        ->all();      
    }


    public static function selectPersonsPolygon($id_mapa){

        // (POLYGONS)

        return DB::table('APS_MAPA_PERSONA')
        ->select(
            'moises.vw_persona_natural.nom_persona',
            'APS_MAPA_PERSONA.ID_PERSONA',
            'APS_MAPA_PERSONA.FECHA')
        ->join('moises.vw_persona_natural','moises.vw_persona_natural.ID_PERSONA','=','APS_MAPA_PERSONA.ID_PERSONA')
        ->where('APS_MAPA_PERSONA.ID_MAPA','=',$id_mapa)
        ->get()
        ->unique('id_persona')
        ->values()
        ->all();
    }


    public static function saveDeptoPolygon($data){
        
        // (POLYGONS)

        DB::table('APS_MAPA_DEPTO')->insert($data);
    }

    public static function savePersonPolygon($data){
        
        // (POLYGONS)

        DB::table('APS_MAPA_PERSONA')->insert($data);
    }

    public static function deletePersonPolygon($id_mapa, $id_persona){

        // (POLYGONS)

        DB::table('APS_MAPA_PERSONA')
        ->where('APS_MAPA_PERSONA.ID_MAPA','=',$id_mapa)
        ->where('APS_MAPA_PERSONA.ID_PERSONA','=',$id_persona)
        ->delete();
    }

    public static function deleteDeptoPolygon($id_depto, $id_entidad, $id_mapa){

        // (POLYGONS)


        DB::table('APS_MAPA_DEPTO')
        ->where('APS_MAPA_DEPTO.ID_DEPTO','=',$id_depto)
        ->where('APS_MAPA_DEPTO.ID_ENTIDAD','=',$id_entidad)        
        ->where('APS_MAPA_DEPTO.ID_MAPA','=',$id_mapa)
        ->delete();
    }


    public static function deletePolygonConfig($id_mapa){

        // (POLYGONS)

        DB::transaction(function() use($id_mapa) {

            DB::table('APS_MAPA_DEPTO')
            ->where('APS_MAPA_DEPTO.ID_MAPA','=',$id_mapa)
            ->delete();

            DB::table('APS_MAPA_PERSONA')
            ->where('APS_MAPA_PERSONA.ID_MAPA','=',$id_mapa)
            ->delete();

            DB::table('APS_MAPA_COORDENADA')
            ->where('APS_MAPA_COORDENADA.ID_MAPA','=',$id_mapa)
            ->delete();

            DB::table('APS_MAPA_POLIGONO')
            ->where('APS_MAPA_POLIGONO.ID_MAPA','=',$id_mapa)
            ->delete();

        });       

    }


    public static function updatePolygonConfig($id_mapa, $id_entidad, $id_depto, $data){

        // (POLYGONS)

        DB::table('APS_MAPA_POLIGONO')
        ->where('APS_MAPA_POLIGONO.ID_MAPA','=',$id_mapa)
        ->where('APS_MAPA_POLIGONO.ID_ENTIDAD','=',$id_entidad)
        ->where('APS_MAPA_POLIGONO.ID_DEPTO','=',$id_depto)
        ->update($data);

    }


    public static function getPersonForPolygon($id_persona){

        // (POLYGONS)

        return DB::table('moises.vw_persona_natural')
        ->select(
            'moises.vw_persona_natural.ID_PERSONA',
            'moises.vw_persona_natural.PATERNO',
            'moises.vw_persona_natural.MATERNO',
            'moises.vw_persona_natural.NOMBRE',
            'moises.vw_persona_natural.NUM_DOCUMENTO')
        ->where('moises.vw_persona_natural.ID_PERSONA','=',$id_persona)
        ->first();
    }


    public static function getUserDevice($id_persona, $uuid){

        // (POLYGONS)
        // AÑADIR FILTRO POR UUID

        return DB::table('APS_USER_DEVICE')
        ->where('APS_USER_DEVICE.ID_PERSONA','=',$id_persona)
        ->where('APS_USER_DEVICE.UUID','=',$uuid)
        ->first();
    }


    public static function saveUserDevice($data){
        // (POLYGONS)
        DB::table('APS_USER_DEVICE')->insert($data);
    }


    public static function ExistPerson($dni) {
        
        // Obtiene el codigo marcación por dni del personal (POLYGONS Y MOVIL)

        return DB::connection('siscop')
        ->table('PERSONAL')
        ->select(
            DB::raw('TO_NUMBER(PERSONAL.FOTOCHECK) AS COD'),
            'PERSONAL.FOTOCHECK AS CODIGO',
            'PERSONAL.IDPERSONAL')
        ->where('PERSONAL.NDOCUMENTO','=',$dni)
        ->first();

    }    


    public static function CanInsertAssist($person) {

        // Verifica si se tomo asistencia pasado los 50 minutos despues de su ultima marcación (POLYGONS)

        // $q = "SELECT 
        //     CASE
        //         WHEN SYSDATE >= MAX(c.fechahora)+50/1440 THEN 'true'
        //         ELSE 'false'
        //     END AS status,
        //     count(c.iidmarcacion) as registros
        //     FROM ASIST.PERSONAL a, ASIST.ZKUSUARIOS b, ASIST.ZKMarcaciones c WHERE a.NDOCUMENTO='".$dni."' AND b.ICODUSUARIO = TO_NUMBER(a.FOTOCHECK) AND c.iidusuario=b.IIDUSUARIO AND c.icodusuario=b.ICODUSUARIO AND TO_CHAR(c.FECHAHORA,'DDMMYYYY') = TO_CHAR(SYSDATE,'DDMMYYYY')";
        // return DB::connection('siscop')->select($q);


        return DB::connection('siscop')
        ->table('ASISTENCIA')
        ->select(DB::raw("CASE 
            WHEN (SYSDATE >= MAX(FECHAHORA)+50/1440) THEN 'true'
            WHEN COUNT(FECHAHORA) = 0 THEN 'true'
            ELSE 'false'
            END AS status"))
        ->where('ASISTENCIA.IDPERSONAL','=',$person->idpersonal)
        ->where('ASISTENCIA.FOTOCHECK','=',$person->codigo)
        ->whereRaw("TO_CHAR(ASISTENCIA.FECHAHORA,'DDMMYYYY') = TO_CHAR(SYSDATE,'DDMMYYYY')")
        ->first();

    }



    public static function SaveAssistanceUser($person, $extra) {


        // Guarda control de asistencia desde movil (POLYGONS)
        $id_marcacion = null;
        $id_asistencia = null;


        $cod = $person->cod;
        $codigo = $person->codigo;
        $idpersonal = $person->idpersonal;


        $status = false;

        $user = DB::connection('siscop')
        ->table('ZKUSUARIOS')
        ->select(
            'ZKUSUARIOS.IIDUSUARIO',
            'ZKUSUARIOS.ICODUSUARIO')
        ->where('ZKUSUARIOS.ICODUSUARIO','=',$cod)
        ->first();

        $now = DB::raw('sysdate');

        $now_date = DB::raw("to_char(sysdate,'YYYY-MM-DD')");
        



        if ($user) {

            $dataMarc = [
                'iidterminal' => '12',
                'inumero' => '4',
                'iidusuario' => $user->iidusuario,
                'icodusuario' => $user->icodusuario,
                'imodoverificacion' => '1',
                'imodoentradasalida' => '0',
                'fechahora' => $now,
                'grabadook' => '1',
                'registro' => $now,
                'cardnumber' => '0',
                'transferido' => '0',
                'intworkcode' => '0'
            ];

            $id_marcacion = DB::transaction(function() use($dataMarc) {
                DB::connection('siscop')->table('ZKMarcaciones')->insert($dataMarc);
                return DB::connection('siscop')->getSequence()->currentValue('SQ_ZKMARCACIONES');
            });

            $status = true;

        }


        if ($idpersonal) {

            $dataAsis = [
                'idpersonal' => $idpersonal,
                'fecha' => $now_date,
                'fechahora' => $now,
                'tipoingreso' => '1',
                'tipomarcacion' => '0',
                'num_marcador' => '37',
                'fotocheck' => $codigo,
                'fecharegistro' => $now
            ];

            $id_asistencia = DB::transaction(function() use($dataAsis) {
                DB::connection('siscop')->table('ASISTENCIA')->insert($dataAsis);
                return DB::connection('siscop')->getSequence()->currentValue('SQ_ASISTENCIA');
            });

            $status = true;
        }        



        if ($id_marcacion && $id_asistencia) {
            $extra['id_marcacion'] = $id_marcacion;
            $extra['id_asistencia'] = $id_asistencia;
            $extra['fecha'] = $now;
            $extra['tipo'] = 'M';
            DB::table('APS_ASISTENCIA_POLIGONO')->insert($extra);
            $status = true;

        } else {

            $status = false;
        }

        return $status;
    }


    public static function AssistanceByUser($dni) {

        // TEST ASSISTANCE (POLYGONS)

        $q = "SELECT c.*
        FROM ASIST.PERSONAL a, ASIST.ZKUSUARIOS b, ASIST.ZKMarcaciones c WHERE a.NDOCUMENTO='".$dni."' AND b.ICODUSUARIO = TO_NUMBER(a.FOTOCHECK) AND c.iidusuario=b.IIDUSUARIO AND c.icodusuario=b.ICODUSUARIO AND TO_CHAR(c.FECHAHORA,'DDMMYYYY') = TO_CHAR(SYSDATE,'DDMMYYYY')";
        return DB::connection('siscop')->select($q);
    }


    public static function ResetAsis($id, $status) {

        // TEST ASSISTANCE (POLYGONS)

        $data = DB::connection('siscop')
        ->table('ZKMarcaciones')
        ->where('ZKMarcaciones.iidmarcacion','=',$id)
        ->get();

        if ($status && count($data) == 1) {

            DB::connection('siscop')
            ->table('ZKMarcaciones')
            ->where('ZKMarcaciones.iidmarcacion','=',$id)
            ->delete();
        }
        
        return $data;
    }



    public static function getUUIDDevicesUser($id_persona, $token){

        // (POLYGONS) // VERIFICAR CON FILTRO DE DEVICE

        $data = null;

        $device = DB::table('APS_USER_DEVICE')
        ->select(
            'APS_USER_DEVICE.ID_PERSONA',
            'APS_USER_DEVICE.ID_USERDEVICE',
            'APS_USER_DEVICE.UUID')
        ->where('APS_USER_DEVICE.UUID','=',$token)
        ->get()
        ->first();

        $devices = DB::table('APS_USER_DEVICE')        
        ->select(
            'APS_USER_DEVICE.UUID')
        ->where('APS_USER_DEVICE.ID_PERSONA','=',$id_persona)
        ->get()
        ->map(function ($item, $key) {
            return $item->uuid;
            });

        if ($device) {
            // si existe device registrado
            $data['device'] = $device->uuid;
            $data['msg'] = 'Dispositivo registrado';


            // El device le pertenece a la persona ?
            if ($device->id_persona == $id_persona) {
                
                $data['isValid'] = true;

                $data['count'] = DB::table('APS_USER_DEVICE')
                ->where('APS_USER_DEVICE.UUID','=',$token)
                ->count();

                // el device es unico ?
                if ($data['count'] == 1) {
                    $data['isValid'] = true;

                    //El device esta en la lista de devices de la persona
                    if (in_array($token, $devices->toArray())) {
                        $data['msg'] = 'El dispositivo OK';
                        $data['isValid'] = true;

                    } else {
                        $data['msg'] = 'El dispositivo no esta en la lista de dispositivos de la persona.';
                        $data['isValid'] = false;
                    }


                } else {
                    $data['msg'] = 'El dispositivo no esta asociado correctamente, comuniquese con DIGETI.';
                    $data['isValid'] = false;
                }

            } else {
                $data['count'] = 1;
                $data['isValid'] = false;
                $data['msg'] = 'El dispositivo ya esta asociado.';
            }
            

        } else {
            $data['device'] = null;
            $data['isValid'] = true;
            $data['count'] = 0;
            $data['msg'] = 'Dispositivo no registrado.';
        }
        


        // $device = DB::table('APS_USER_DEVICE')
        // ->select('APS_USER_DEVICE.UUID')
        // ->where('APS_USER_DEVICE.ID_PERSONA','=',$id_persona)
        // ->first();

        // if ($device) {

        //     $uuid = $device->uuid;

        //     $data['device'] = $uuid;

        //     $data['count'] = DB::table('APS_USER_DEVICE')
        //     ->where('APS_USER_DEVICE.UUID','=',$uuid)
        //     ->count();

        //     if ($data['count'] == 1) {
        //         $data['isValid'] = true;
        //     }else {
        //         $data['isValid'] = false;
        //     }

        //     if ($token != $uuid) {
        //         $data['isValid'] = false;
        //     }

        // }

        return $data;
    }


    public static function AssistanceUser($dni, $fecha) {

        // ASSISTANCE USER (MOVIL)

        // $q = "SELECT c.iidmarcacion  as id, c.fechahora as fecha
        // FROM ASIST.PERSONAL a, ASIST.ZKUSUARIOS b, ASIST.ZKMarcaciones c WHERE a.NDOCUMENTO='$dni' AND b.ICODUSUARIO = TO_NUMBER(a.FOTOCHECK) AND c.iidusuario=b.IIDUSUARIO AND c.icodusuario=b.ICODUSUARIO AND TO_CHAR(c.FECHAHORA,'YYYYMMDD') = $fecha";
        // return DB::connection('siscop')->select($q);


        return DB::connection('siscop')
        ->table('ASISTENCIA')
        ->join('PERSONAL', 'ASISTENCIA.IDPERSONAL', '=', 'PERSONAL.IDPERSONAL')
        ->select(
            'ASISTENCIA.IDASISTENCIA AS ID',
            'ASISTENCIA.FECHAHORA AS FECHA',
            DB::raw("TO_CHAR(ASISTENCIA.FECHAHORA,'hh24:mi:ss AM') AS HORA")            
        )
        ->where('PERSONAL.NDOCUMENTO','=',$dni)
        ->whereRaw("TO_CHAR(ASISTENCIA.FECHAREGISTRO,'YYYYMMDD') = $fecha")
        ->get();



    }


    public static function AssistPolyUser($id_persona, $ids_marcacion) {

        // Asistencia dentro de un poligono de usuario (MOVIL)

        return DB::table('APS_ASISTENCIA_POLIGONO')
        ->join('APS_MAPA_POLIGONO', 'APS_ASISTENCIA_POLIGONO.ID_MAPA', '=', 'APS_MAPA_POLIGONO.ID_MAPA')
        ->select(
            'APS_ASISTENCIA_POLIGONO.ID_ASISTENCIA as ID',
            'APS_ASISTENCIA_POLIGONO.LAT',
            'APS_ASISTENCIA_POLIGONO.LNG',
            'APS_MAPA_POLIGONO.NOMBRE')
        ->where('APS_ASISTENCIA_POLIGONO.ID_PERSONA','=',$id_persona)
        ->whereIn('APS_ASISTENCIA_POLIGONO.ID_ASISTENCIA', $ids_marcacion)
        ->get();
    }


    public static function DevicesUserData($query) {
        return DB::table('APS_USER_DEVICE')
        ->join('MOISES.VW_PERSONA_NATURAL', 'APS_USER_DEVICE.ID_PERSONA', '=', 'MOISES.VW_PERSONA_NATURAL.ID_PERSONA')
        ->select(
        'APS_USER_DEVICE.ID_USERDEVICE AS ID',
        'MOISES.VW_PERSONA_NATURAL.NOM_PERSONA',
        'MOISES.VW_PERSONA_NATURAL.NOMBRE',
        'MOISES.VW_PERSONA_NATURAL.PATERNO',
        'MOISES.VW_PERSONA_NATURAL.MATERNO',
        'MOISES.VW_PERSONA_NATURAL.NUM_DOCUMENTO',
        'APS_USER_DEVICE.MODEL',
        'APS_USER_DEVICE.PLATFORM',
        'APS_USER_DEVICE.UUID',
        'APS_USER_DEVICE.VERSION',
        'APS_USER_DEVICE.CAN_RESET_TOUCH_ID',
        'APS_USER_DEVICE.RE_ASIGN',
        'APS_USER_DEVICE.STATE')
        ->whereRaw("UPPER(MOISES.VW_PERSONA_NATURAL.NOMBRE) LIKE UPPER('%{$query}%') OR UPPER(MOISES.VW_PERSONA_NATURAL.PATERNO||MOISES.VW_PERSONA_NATURAL.MATERNO) LIKE REPLACE(UPPER('%{$query}%'),' ', '')")
        ->orWhere('MOISES.VW_PERSONA_NATURAL.NUM_DOCUMENTO', '=', $query)
        ->paginate(10);
    }

    public static function DevicesUserUpdate($id, $data){
        return DB::table('APS_USER_DEVICE')
        ->where('APS_USER_DEVICE.ID_USERDEVICE','=',$id)
        ->update($data);

    }

    public static function reportsAssistances($id_entidad,$id_depto,$fecha_de,$fecha_a){
        $date_1 = explode("/",$fecha_de);
        $date_de = $date_1[2]."/".$date_1[1]."/".$date_1[0];
        $date_2 = explode("/",$fecha_a);
        $date_a = $date_2[2]."/".$date_2[1]."/".$date_2[0];
        
        
        $query = "SELECT SUM(NVL(ASISTENCIA,0)/NVL(DECODE(FC_DIAS_LABORABLES('".$fecha_de."','".$fecha_a."'),0,1),1)) ASIST,
                        SUM(NVL(ASISTENCIA_CULTO,0)/NVL(DECODE(FC_DIAS_LABORABLES('".$fecha_de."','".$fecha_a."'),0,1),1)) ASIST_CULTO,
                        COUNT(*)/NVL(DECODE(FC_DIAS_LABORABLES('".$fecha_de."','".$fecha_a."'),0,1),1) AS TOTAL,
                        ROUND(SUM(NVL(ASISTENCIA,0)/NVL(DECODE(FC_DIAS_LABORABLES('".$fecha_de."','".$fecha_a."'),0,1),1))/COUNT(*)/NVL(DECODE(FC_DIAS_LABORABLES('".$fecha_de."','".$fecha_a."'),0,1),1),2) P_ASIST,
                        ROUND(SUM(DECODE(NVL(ASISTENCIA,0)/NVL(DECODE(FC_DIAS_LABORABLES('".$fecha_de."','".$fecha_a."'),0,1),1),0,1,0))/COUNT(*)/NVL(DECODE(FC_DIAS_LABORABLES('".$fecha_de."','".$fecha_a."'),0,1),1),2) P_NOASIST,
                        ROUND(SUM(NVL(ASISTENCIA_CULTO,0)/NVL(DECODE(FC_DIAS_LABORABLES('".$fecha_de."','".$fecha_a."'),0,1),1))/COUNT(*)/NVL(DECODE(FC_DIAS_LABORABLES('".$fecha_de."','".$fecha_a."'),0,1),1),2) P_ASIST_CULTO,
                        ROUND(SUM(DECODE(NVL(ASISTENCIA_CULTO,0)/NVL(DECODE(FC_DIAS_LABORABLES('".$fecha_de."','".$fecha_a."'),0,1),1),0,1,0))/COUNT(*)/NVL(DECODE(FC_DIAS_LABORABLES('".$fecha_de."','".$fecha_a."'),0,1),1),2) P_NOASIST_CULTO
                FROM APS_CONTROL_CULTO P
                WHERE ID_ENTIDAD = ".$id_entidad."
                AND ID_DEPTO = '".$id_depto."'
                AND TO_CHAR(P.FEC_FECHA,'YYYY/MM/DD') BETWEEN '".$date_de."' AND '".$date_a."' ";
        //dd($query);
        $oQuery = DB::select($query);        
        return $oQuery;
    }


    public static function UsersBackGeo($params){

        return DB::table('MOISES.VW_PERSONA_NATURAL')
            ->select(
                'MOISES.VW_PERSONA_NATURAL.ID_PERSONA',
                'MOISES.VW_PERSONA_NATURAL.PATERNO',
                'MOISES.VW_PERSONA_NATURAL.MATERNO',
                'MOISES.VW_PERSONA_NATURAL.NOMBRE',
                'APS_USER_DEVICE.UUID AS TOKEN')
            ->join('APS_TRABAJADOR', 'MOISES.VW_PERSONA_NATURAL.ID_PERSONA', '=', 'APS_TRABAJADOR.ID_PERSONA')
            ->leftJoin('APS_USER_DEVICE', 'MOISES.VW_PERSONA_NATURAL.ID_PERSONA', '=', 'APS_USER_DEVICE.ID_PERSONA')
            ->where('APS_TRABAJADOR.ID_DEPTO','=',$params['id_depto'])
            // ->whereNotIn('VW_PERSONA_NATURAL.ID_PERSONA', [$params['id_user']])
            ->get();
    }



    public static function AreasResp($params){

        return DB::table('ORG_AREA_RESPONSABLE')
            ->select(
                'ORG_SEDE_AREA.ID_DEPTO',
                'ORG_AREA.NOMBRE')
            ->join('ORG_SEDE_AREA', 'ORG_AREA_RESPONSABLE.ID_SEDEAREA', '=', 'ORG_SEDE_AREA.ID_SEDEAREA')
            ->join('ORG_AREA', 'ORG_SEDE_AREA.ID_AREA', '=', 'ORG_AREA.ID_AREA')
            ->where('ORG_AREA_RESPONSABLE.ID_PERSONA','=',$params['id_persona'])            
            ->where('ORG_SEDE_AREA.ID_ENTIDAD','=',$params['id_entidad'])
            ->get();
    }




    public static function AssistancePersonDevice($id_entidad,$id_depto,$id_persona ){

        // asistencia de personal por departamento y estado de notificacion (APP MOVIL)


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
        
        $pdo = DB::getPdo();
        
        $stmt = $pdo->prepare("begin PKG_HUMAN_TALENT.SP_GENERA_CONTROL_CULTO(
                                        :ID_ENTIDAD
                                     ); end;");
        $stmt->bindParam(':ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
    
        $stmt->execute(); 
        
        $return = ['nerror'=>0];
        
        if (($dias>=2 and $dias<=6) and ($horas>=6 and $horas<=20)){
            
            AssistanceData::actualizarMarcacion($id_entidad,$id_depto,$id_persona);
            if($id_depto == "0"){
                
                $depto = "AND P.ID_DEPTO IN (select b.ID_DEPTO from ORG_AREA_RESPONSABLE a , ORG_SEDE_AREA b
                        WHERE a.ID_PERSONA=".$id_persona."
                        and b.ID_ENTIDAD=".$id_entidad."
                        and a.ID_SEDEAREA = b.ID_SEDEAREA)";

            }else{
                $depto = "AND P.ID_DEPTO = '".$id_depto."' ";
            }
            
            $sql="SELECT 
                P.ID_CONTROL_CULTO,
                D.NOMBRE,
                D.PATERNO,
                D.MATERNO,
                P.ASISTENCIA,
                P.ASISTENCIA_CULTO,
                (CASE WHEN P.ASISTENCIA_CULTO = 1 THEN 'true' ELSE 'false' END) as ASISTENCIA_CULTO_BOOL,
                P.FECHA_MARCACION,
                FC_DOCUMENTO_CLIENTE(D.ID_PERSONA) DNI ,
                D.ID_PERSONA,
                (CASE WHEN EXISTS(
                 SELECT ud.NOT_ASSIT FROM APS_USER_DEVICE ud where ud.ID_PERSONA = P.ID_PERSONA and ud.NOT_ASSIT = 1)
                 THEN 'true'
                 ELSE 'false' END
                 ) as NOT_ASSIT,
                 (CASE WHEN EXISTS(
                 SELECT ud.NOT_ASSIT FROM APS_USER_DEVICE ud where ud.ID_PERSONA = P.ID_PERSONA)
                 THEN 'SI'
                 ELSE 'NO' END
                 ) as DEVICE_EXISTS
            FROM APS_CONTROL_CULTO P, MOISES.VW_PERSONA_NATURAL_TRABAJADOR D
            WHERE P.ID_PERSONA = D.ID_PERSONA
            AND P.ID_ENTIDAD=".$id_entidad." 
            ".$depto." 
            AND D.ESTADO = 'A'
            AND TO_CHAR(P.FEC_FECHA,'DDMMYYYY') = TO_CHAR(SYSDATE,'DDMMYYYY') 
            ORDER BY D.PATERNO,D.MATERNO,D.NOMBRE ";
        
            $data = DB::select($sql);
            
            $return = ['nerror'=>0,'mensaje'=>'','data'=>$data];
            
        }else{
            $return = ['nerror'=>1,'mensaje'=>'Solo disponible de 7 a 8 am','data'=>[]];
        }
        return $return;
    }




    public static function AssistancePersonForNotAssit($id_entidad,$id_depto,$id_persona){

        // Array de id de persona por departamento para cambiar su estado 
        // de recibir notificacion de asistencia (APP MOVIL)

        if($id_depto == "0"){
            $depto = "AND P.ID_DEPTO IN (select b.ID_DEPTO from ORG_AREA_RESPONSABLE a , ORG_SEDE_AREA b
                        WHERE a.ID_PERSONA=".$id_persona."
                        and b.ID_ENTIDAD=".$id_entidad."
                        and a.ID_SEDEAREA = b.ID_SEDEAREA)";
        } else {
            $depto = "AND P.ID_DEPTO = '".$id_depto."' ";
        }

        $sql="SELECT D.ID_PERSONA 
            FROM APS_CONTROL_CULTO P, MOISES.VW_PERSONA_NATURAL_TRABAJADOR D
            WHERE P.ID_PERSONA = D.ID_PERSONA
            AND P.ID_ENTIDAD=".$id_entidad." 
            ".$depto." 
            AND D.ESTADO = 'A'
            AND TO_CHAR(P.FEC_FECHA,'DDMMYYYY') = TO_CHAR(SYSDATE,'DDMMYYYY')
            AND EXISTS(
                 SELECT ud.NOT_ASSIT FROM APS_USER_DEVICE ud where ud.ID_PERSONA = P.ID_PERSONA) 
            ORDER BY D.PATERNO,D.MATERNO,D.NOMBRE";
        
        return collect(DB::select($sql))->map(function ($obj) {return $obj->id_persona;});
    }


    public static function updateNotifAssit($ids, $data){

        // Actualiza columna NOT_ASSIT de APS_USER_DEVICE de personas
        return DB::table('APS_USER_DEVICE')
        ->whereIn('APS_USER_DEVICE.ID_PERSONA',$ids)
        ->update($data);

    }
        
    


}