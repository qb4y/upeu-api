<?php
namespace App\Http\Data\Setup\Email;
use Exception;
// use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailData {
    public static function listEmails($id_entidad){
        $getResult = DB::table('ELISEO.CONTA_ENTIDAD_EMAIL_CONFIG')
                    ->select('ID_EMAIL','ID_ENTIDAD','ALIAS','NOMBRE','HOST','USERNAME','CONTRASENHA','SMTPSECURE','ENCRYPTION','PORT','ESTADO')
                    ->where('ID_ENTIDAD','=',$id_entidad)
                    ->get();        
        return $getResult;
    }

    
    public static function showEmail($id_email){
        $getResult = DB::table('ELISEO.CONTA_ENTIDAD_EMAIL_CONFIG')
                    ->select('ID_EMAIL','ID_ENTIDAD','ALIAS','NOMBRE','HOST','USERNAME','CONTRASENHA','SMTPSECURE','ENCRYPTION','PORT','ESTADO')
                    ->where('ID_EMAIL','=',$id_email)
                    ->get();        
        return $getResult;
    } 

    public static function existEmailAlias($id_entidad,$alias,$id_email){
        $getResult = DB::table('ELISEO.CONTA_ENTIDAD_EMAIL_CONFIG')
                    ->select('ID_EMAIL','ID_ENTIDAD','ALIAS','NOMBRE','HOST','USERNAME','CONTRASENHA','SMTPSECURE','ENCRYPTION','PORT','ESTADO')
                    ->where('ID_ENTIDAD','=',$id_entidad)
                    ->where('ALIAS','=',$alias)
                    ->where('ID_EMAIL','<>',$id_email)
                    ->first();        
        return $getResult;
    } 

    public static function showEmailAlias($id_entidad,$alias){
        $getResult = DB::table('ELISEO.CONTA_ENTIDAD_EMAIL_CONFIG')
                    ->select('ID_EMAIL','ID_ENTIDAD','ALIAS','NOMBRE','HOST','USERNAME','CONTRASENHA','SMTPSECURE','ENCRYPTION','PORT','ESTADO')
                    ->where('ID_ENTIDAD','=',$id_entidad)
                    ->where('ALIAS','=',$alias)
                    ->first();        
        return $getResult;
    } 
    
    public static function addEmail($data,$id_entidad){
        $id_entidad=$data->id_entidad?$data->id_entidad:$id_entidad;
        $alias=$data->alias?$data->alias:null;
        $nombre=$data->nombre?$data->nombre:null;
        $host=$data->host?$data->host:null;
        $username=$data->username?$data->username:null;
        $contrasenha=$data->contrasenha?$data->contrasenha:null;
        $smtpsecure=$data->smtpsecure?$data->smtpsecure:null;
        $encryption=$data->encryption?$data->encryption:null;
        $port=$data->port?$data->port:null;
        $estado=$data->estado?$data->estado:'0';
        $arrayInsert= ['ID_ENTIDAD' => $id_entidad, 
        'ALIAS' => $alias,
        'NOMBRE' => $nombre,
        'HOST' => $host,
        'USERNAME' => $username,
        'CONTRASENHA' => $contrasenha,
        'SMTPSECURE' => $smtpsecure,
        'ENCRYPTION' => $encryption,
        'PORT' => $port,
        'ESTADO' => $estado];
        DB::table('ELISEO.CONTA_ENTIDAD_EMAIL_CONFIG')->insert($arrayInsert);
        return $arrayInsert;
    }
    public static function updateEmail($data,$id_email){
        $id_entidad=$data->id_entidad?$data->id_entidad:null;
        $alias=$data->alias?$data->alias:null;
        $nombre=$data->nombre?$data->nombre:null;
        $host=$data->host?$data->host:null;
        $username=$data->username?$data->username:null;
        $contrasenha=$data->contrasenha?$data->contrasenha:null;
        $smtpsecure=$data->smtpsecure?$data->smtpsecure:null;
        $encryption=$data->encryption?$data->encryption:null;
        $port=$data->port?$data->port:null;
        $estado=$data->estado?$data->estado:'0';
        DB::table('ELISEO.CONTA_ENTIDAD_EMAIL_CONFIG')
            ->where('ID_EMAIL', $id_email)
            ->update([
                'ID_ENTIDAD' => $id_entidad,
                'ALIAS' => $alias,
                'NOMBRE' => $nombre,
                'HOST' => $host,
                'USERNAME' => $username,
                'CONTRASENHA' => $contrasenha,
                'SMTPSECURE' => $smtpsecure,
                'ENCRYPTION' => $encryption,
                'PORT' => $port,
                'ESTADO' => $estado
            ]);
        $getResult = DB::table('ELISEO.CONTA_ENTIDAD_EMAIL_CONFIG')
        ->select('ID_EMAIL','ID_ENTIDAD','ALIAS','NOMBRE','HOST','USERNAME','CONTRASENHA','SMTPSECURE','ENCRYPTION','PORT','ESTADO')
        ->where('ID_EMAIL','=',$id_email)
        ->get();           
        return $getResult;
    }

    public static function deleteEmail($id_email){
        DB::table('ELISEO.CONTA_ENTIDAD_EMAIL_CONFIG')
        ->where('ID_EMAIL','=',$id_email)
        ->delete();
    }
}