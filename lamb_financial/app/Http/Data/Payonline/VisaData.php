<?php
namespace App\Http\Data\Payonline;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
class VisaData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function listVisa(){
        $sql = "select 
                id_visa,
                merchantid,
                accesskey,
                secretkey,
                prod,
                descripcion
            from payonline_visa 
            order by id_visa";
        $query = DB::select($sql);
        
        return $query;
    }
    public static function showVisa($id_visa){
        $sql = "select 
                id_visa,
                merchantid,
                accesskey,
                secretkey,
                prod,
                descripcion
            from payonline_visa 
            where id_visa=".$id_visa;
        $query = DB::select($sql);
        
        return $query;
    }
    public static function showVisaError($id_visa_error){
        $sql = "select 
                id_visa_error,
                desapoyo,
                descliente
            from payonline_visa_error 
            where id_visa_error=".$id_visa_error;
        $query = DB::select($sql);
        
        return $query;
    }
    public static function ordenVisa($id_visa){
        $sql = "select 
                id_visa_orden,
                id_visa,
                num_orden
            from payonline_visa_orden 
            where vigencia=1
            and id_visa=".$id_visa;
        $query = DB::select($sql);
        
        $num_orden = 0;
        foreach($query as $row){
               $num_orden = $row->num_orden;
        }
        $num_orden++;
        
        $query = "update payonline_visa_orden SET 
                    num_orden =".$num_orden."
                where vigencia=1
                and id_visa=".$id_visa;
        
        DB::update($query);
        
        return $num_orden;
    }
    
    public static function visaLog($id_personal,$errorCode,$mensaje,$json_visa,$nerrorservice,$json_servicio,$nerrorrpta,$repuesta_pta,$ip,$importe,$id_visa,$id_aplicacion,$id_operacion){
        
        $sql = "select 
                coalesce(max(id_visa_log),0)+1 as id
            from PAYONLINE_VISA_LOG";
        $query = DB::select($sql);
        $id_visa_log = 0;
        foreach($query as $row){
            $id_visa_log = $row->id;
        }
        $sql = "select 
                TO_CHAR(sysdate, 'YYYY-MM-DD HH24:MI:SS') as fecha
            from dual";
        $query = DB::select($sql);
        $fecha_sys = date("Y-m-d H:i:s");
        foreach($query as $row){
            $fecha_sys = $row->fecha;
        }
        //$fecha = date("d/m/Y H:i:s");
        //$fecha_sys=date("Y-m-d H:i:s");
        DB::table('PAYONLINE_VISA_LOG')->insert(
            array('id_visa_log' => $id_visa_log,
                'codigo_persona' =>$id_personal,
                'rpta_visa' =>substr($json_visa,0,1499),
                'id_visa_error' =>$errorCode,
                'mensaje' =>substr($mensaje,0,499),
                'rpta_servicio' =>substr($json_servicio,0,499),
                'nerror_servicio' =>$nerrorservice,
                'rpta_respuesta' =>substr($repuesta_pta,0,1499),
                'nerror_respuesta' =>$nerrorrpta,
                'ip'=>$ip,
                'fecha'=>$fecha_sys,
                'importe' =>$importe,
                'id_visa' =>$id_visa,
                'id_aplicacion' =>$id_aplicacion,
                'id_operacion' =>$id_operacion
                )
        );
    }
}



