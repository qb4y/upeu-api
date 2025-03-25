<?php
namespace App\Http\Data\Payonline;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
class McData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    public static function listMc(){
        $sql = "select 
                id_mc,
                codcomercio,
                merchantkey,
                prod,
                descripcion
            from payonline_mc 
            order by id_mc";
        $query = DB::select($sql);
        
        return $query;
    }
    public static function showMc($id_mc){
        $sql = "select 
                id_mc,
                codcomercio,
                merchantkey,
                prod,
                descripcion
            from payonline_mc 
            where id_mc=".$id_mc;
        $query = DB::select($sql);
        
        return $query;
    }
    public static function showMcError($codigo,$tipo){
        $sql = "select 
                id_mc_error,
                codigo,
                descripcion,
                accion1,
                accion2,
                accion3,
                tipo
            from payonline_mc_error 
            where codigo=".$codigo." 
            and tipo='".$tipo."'";
        $query = DB::select($sql);
        
        return $query;
    }
     public static function ordenMc($id_mc){
        $sql = "select 
                id_mc_orden,
                id_mc,
                num_orden
            from payonline_mc_orden 
            where vigencia=1
            and id_mc=".$id_mc;
        $query = DB::select($sql);
        
        $num_orden = 0;
        foreach($query as $row){
               $num_orden = $row->num_orden;
        }
        $num_orden++;
        
        $query = "update payonline_mc_orden SET 
                    num_orden =".$num_orden."
                where vigencia=1
                and id_mc=".$id_mc;
        
        DB::update($query);
        
        return $num_orden;
    }   
    public static function mcLog($id_personal,$errorCode,$mensaje,$json_mc,$nerrorservice,$json_servicio,$nerrorrpta,$repuesta_pta,$ip){
        
        $sql = "select 
                coalesce(max(id_mc_log),0)+1 as id
            from PAYONLINE_MC_LOG";
        $query = DB::select($sql);
        $id_mc_log = 0;
        foreach($query as $row){
            $id_mc_log = $row->id;
        }
        
        //$fecha = date("d/m/Y H:i:s");
        $fecha_sys=date("Y-m-d H:i:s");
        DB::table('PAYONLINE_MC_LOG')->insert(
            array('id_mc_log' => $id_mc_log,
                'codigo_persona' =>$id_personal,
                'rpta_visa' =>$json_mc,
                'id_visa_error' =>$errorCode,
                'mensaje' =>$mensaje,
                'rpta_servicio' =>$json_servicio,
                'nerror_servicio' =>$nerrorservice,
                'rpta_respuesta' =>$repuesta_pta,
                'nerror_respuesta' =>$nerrorrpta,
                'ip'=>$ip,
                'fecha'=>$fecha_sys
                )
        );
    }
}

