<?php
namespace App\Http\Data\Sales;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ComunData extends Controller
{
    public function __construct()
    {
        
    }
    public static  function correlativo($tabla,$columna='id',$pcolumna1=array(), $pcolumna2=array()){
        
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
       
    public static function enviarCorreo($view,$data,$email,$asunto){

        Mail::send($view, $data, function($message) use($asunto,$email){
               $message->subject($asunto);
               $message->to($email);
              
        });

    }
        
    public static function fechasystem(){
        return date("Y-m-d H:i:s");
    }
    public static function fnBuscar($dato){
        $q ="LOWER(translate(".$dato.", 'áéíóúàèìòùãõâêîôôäëïöüçñÁÉÍÓÚÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇÑ','aeiouaeiouaoaeiooaeioucnAEIOUAEIOUAOAEIOOAEIOUCN'))";
        return $q;
    }
 
    public static  function getParameter($codigo,$id_entidad,$id_anho){
        
        $object = DB::table('plla_parametros_valor as a')
                ->join('plla_parametros as b','b.id_parametro','=','a.id_parametro')
                ->where('b.codigo',$codigo)
                ->where('a.id_entidad',$id_entidad)
                ->where('a.id_anho',$id_anho)
                ->select(DB::raw("coalesce(a.importe,0) as importe"))
                ->first();
        $importe = 0;
        if(!empty($object)){
            $importe = $object->importe*1;
        }

        return $importe;
    }
    public static  function fncorrelativofecha($datos=''){
        if(strlen($datos)>0){
            return date('YmdHis').$datos;
        }else{
            return date('YmdHis');
        }
     }
     public static function ruta_url($url){
        //  dd($url);
        $dat = ComunData::archivosList("HTTP");
        $protocol =$dat["valor"];
        $url_ori = str_replace("http://",$protocol."://", $url);
        return $url_ori;
    }
    private static function archivosList($id_config){
        $sql = "SELECT
                    VALOR,
                    VALOR1
                FROM APS_CONFIG_PLANILLA
                WHERE ID_CONFIG='".$id_config."' ";
        $query = DB::select($sql);
        
        
        
        $return["valor"]='';
        $return["valor1"]='';
        
        foreach( $query as $row){
            $return["valor"]=$row->valor;
            $return["valor1"]=$row->valor1;
        }
        
        return $return;
        
    }
    public static function uploadFile($file, $folder, $filename = '')
    {

        $dir = ComunData::directory($folder);


        $request = ['nerror' => 1, 'message' => 'No se ha procesado', 'filename' => ''];

        if ($dir['nerror'] == 1) {
            $request = ['nerror' => 1, 'message' => $dir['message'], 'filename' => ''];
        } else {
            $destination = $dir['directory'];
            if ($file) {
                if ($filename != null && $filename != "" && $filename != "null") {
                    if (file_exists($destination . '/' . $filename)) {
                        unlink($destination . '/' . $filename);
                    }
                } else {
                    $ext = $file->getClientOriginalExtension();
                    $filename = uniqid() . '.' . $ext;
                }
                $file->move($destination, $filename);
                $request = ['nerror' => 0, 'message' => 'Se ha procesado correctamente', 'filename' => $filename];
            }
        }


        return $request;
    }
    public static function directory($folder)
    {
        $return = [
            'nerror' => 1,
            'message' => '',
            'directory' => ''
        ];

        // $dirgen = dirname(__DIR__, 4) . '/files_lamb_talent/files_lt';
        $dirgen = dirname(__DIR__, 4). '/data_lamb_financial';
        $folders = explode('/', $folder);
        $error = 0;
        $dirfol = $dirgen;
        // dd($dirfol);
        $r = '';
        foreach ($folders as $fol) {

            $dirfol .=  '/' . $fol;

            if (!file_exists($dirfol)) {

                mkdir($dirfol, 0777);
            }
            if (!file_exists($dirfol)) {
                $error++;
            }
        }

        if ($error > 0) {
            $return = [
                'nerror' => 1,
                'msgerror' => 'Carpeta no se puede crear',
                'directory' => ''
            ];

            return $return;
        }
        $return = [
            'nerror' => 0,
            'msgerror' => '',
            'directory' => $dirfol
        ];
        return $return;
    }
}


