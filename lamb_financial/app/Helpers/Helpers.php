<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Helpers
{
    static public function getIPLocalClient(){ /// obtener la ip local del cliente.
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_X_FORWARDED')) {
            $ip = getenv('HTTP_X_FORWARDED');
        } elseif (getenv('HTTP_FORWARDED_FOR')) {
            $ip = getenv('HTTP_FORWARDED_FOR');
        } elseif (getenv('HTTP_FORWARDED')) {
            $ip = getenv('HTTP_FORWARDED');
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    public static function fnBuscar($dato)
    {
        $q = "translate(UPPER(" . $dato . "), 'áéíóúàèìòùãõâêîôôäëïöüçñÁÉÍÓÚÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇÑ','aeiouaeiouaoaeiooaeioucnAEIOUAEIOUAOAEIOOAEIOUCN')";
        return $q;
    }
    public static function config($id_config)
    {
        $object = DB::table('eliseo.fin_config')
            ->where('id_config', $id_config)
            ->select('valor', 'valor1')
            ->first();

        $return["valor"] = '';
        $return["valor1"] = '';

        if (!empty($object)) {
            $return["valor"] = $object->valor;
            $return["valor1"] = $object->valor1;
        }

        return $return;
    }
    public static function msgError($msg)
    {
        $dato = '';
        $mensaje = '';

        if (is_object($msg)) {
            $mensaje .= 'Cod: ' . $msg->getCode() . ' ';
            $mensaje .= 'File: ' . $msg->getFile() . ' ';
            $mensaje .= 'Line: ' . $msg->getLine() . ' ';
            $dat = self::config("ERROR");
            if ($dat['valor'] == 'S') {
                $mensaje .= 'Message: ' . $msg->getMessage();
            } else {
                $mensaje .= 'Message: Internal error, Help!!!';

            }
        } else {
            $mensaje = $msg;
        }

        $dato = $mensaje;
        return $dato;
    }
    public static  function correlativo($tabla,$columna='id',$pcolumna1=array(), $pcolumna2=array())
    {

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
    public static function fotoUser($url_foto, $minio_budget)
    {
        if (env('APP_ENV') == 'local') {

            if (!empty($url_foto)) {
                $sep = env('FOTOUSERSEPARE');
                $picture = explode($sep, $url_foto);

                $slash = (count($picture) > 1 and $picture[1]) ? env('FOTOUSERUNIR').$picture[1] : env('FOTOUSERUNIR').'sinfoto';
                $foto = $slash.env('FOTOUSEREXT');
            } else {
                $foto = env('FOTOUSERUNIR').'sinfoto'.env('FOTOUSEREXT');
            }
        } else {
            $foto = $url_foto;
        }
        return self::fotoMinio($foto, $minio_budget);
    }
    public static function fotoMinio($foto, $minio_budget)
    {
        $storage = Storage::disk($minio_budget);

        if (!empty($foto) && $storage->exists($foto)) {
            $picture = $storage->temporaryUrl($foto, Carbon::now()->addDay(7));
        } else {
            if (env('APP_ENV') == 'local') {
                $foto = env('FOTOUSERUNIR').'sinfoto'.env('FOTOUSEREXT');
            } else {
                $foto = 'general-secretay/fotodb/sinfoto.jpg';
            }
            if (!empty($foto) && $storage->exists($foto)) {
                $picture = $storage->temporaryUrl($foto, Carbon::now()->addDay(7));
            } else {
                $picture = null;
            }
        }
        return $picture;
    }
}
