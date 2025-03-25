<?php
namespace App\Http\Data;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Session;
//use Crypt;

class GlobalMethods extends Controller{
    public static function personaID($token){
        $oQuery = DB::table('USERS_SESSION')->select('ID_USER')->where('TOKEN', $token)->where('STATUS', '1')->get();

        $id_user = 0;
        foreach ($oQuery as $item){
            $id_user = $item->id_user;
        }
        return $id_user;
    }
    public static function personaIDAPP($token){
        $oQuery = DB::table('USERS_SESSION_ACADEMICO')->select('PER_ID')->where('TOKEN', $token)->where('STATUS', '1')->get();

        $id_user = 0;
        foreach ($oQuery as $item){
            $id_user = $item->per_id;
        }
        return $id_user;
    }
    public static function ipClient($request){
        //$request = Request::instance();
        //return $request->getClientIp();
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP ) !== false){
                    //if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }

    }
    public static function authorizationLamb($request){
        $id_entidad = 0;
        $id_depto = "";
        $email = "";
        $token = $request->header('Authorization');
        $id_user=0;
        $depto_padre='';
        $empresa='';

        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida', $bindings);
            $val = $result[0];
            $valida=$val->active;
            $id_entidad=$val->id_entidad;
            $id_depto=$val->id_depto;
            $email = $val->email;
            //$id_almacen=$val->id_almacen;
            $id_user = GlobalMethods::personaID($token);


            $object = DB::table('conta_entidad_depto')->select('nombre')->where('id_entidad', $id_entidad)->whereraw("substr(id_depto,1,1)=substr('".$id_depto."',1,1)")->first();

            if(!empty($object)){
                $depto_padre = $object->nombre;
            }

            $query = "SELECT m.ID_EMPRESA, p.NOMBRE FROM eliseo.CONTA_ENTIDAD e, eliseo.CONTA_EMPRESA m, moises.VW_PERSONA_JURIDICA p
            WHERE e.ID_EMPRESA = m.ID_EMPRESA
            AND m.ID_RUC = p.ID_RUC
            AND e.ID_ENTIDAD = $id_entidad ";
            $datos_empresa = DB::select($query);
            $empresa = $datos_empresa[0]->nombre;
            $id_empresa = $datos_empresa[0]->id_empresa;


        }else{
            $valida = "NO";
        }

        $code = "401";

        $session = ['depto'=>$depto_padre,'user'=>$email, 'empresa'=>$empresa, 'id_entidad'=>$id_entidad, 'id_empresa'=>$id_empresa ];

        Session::put('datosPrint', $session);

        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado ',
            'data' => [],
            'valida'=>$valida,
            'code'=>$code,
            'id_user'=>$id_user,
            'id_entidad'=>$id_entidad,
            'id_depto'=>$id_depto,
            'email'=>$email,
            //'id_almacen'=>$id_almacen,
            'token'=>$token
        ];

        return $jResponse;
    }

    public static function logDelete($data){

        $tabla=$data["tabla"];
        $ids=$data["where"];
        $pks=$data["pk"];
        $columnas=$data["columna"];
        $id_user=$data["id_user"];
        $oQuery=$data["dataeliminar"];
        $columna=  implode("|", $columnas);

        $i=0;
        $pk="";
        $id="";
        foreach($pks as $c){
            if($i==0){
                $id=$c;
                $pk="to_char(".$c.")";
            }else{
                $id.="+".$c;
                $pk.="||to_char(".$c.")";
            }
            $i++;
        }
        $col="";
        $i=0;
        foreach($columnas as $c){
            if($i==0){
                $col="to_char(".$c.") as ".$c;
            }else{
                $col.=",to_char(".$c.") as ".$c;
            }
            $i++;
        }

        $idvalor="";
        $where="";
        $i=0;
        foreach($ids as $key=>$value){
            if($i==0){
                $where=" ".$key."='".$value."'";
            }else{
                $where.=" and ".$key."='".$value."'";
            }
            $i++;
        }
        /*$query = "SELECT ".$pk." as pktabla, ".$col."
                FROM ".$tabla."
                WHERE ".$where;
        $oQuery = (object)DB::select($query);*/
        $actual="";
        $p=0;
        $j=0;
        foreach($oQuery as $key=>$value){
            $p=0;
            $actual="";
            $dat=(array)$value;
            $idvalor=$dat["pktabla"];
            foreach($columnas as $ke=>$val){
                    if($p==0){
                        $actual=$dat[strtolower($val)];
                    }else{
                        $actual.="|".$dat[strtolower($val)];
                    }
                    $p++;

            }

            $bindings = [
                'P_tabla' => $tabla,
                'P_id' => $id,
                'P_idvalor'=>$idvalor,
                'p_idpersona'=>$id_user,
                'P_ip'=>  \Request::ip(),
                'P_columna'=>$columna,
                'P_actual'=>$actual
            ];
            DB::executeProcedure('sp_audit_tabla_del', $bindings);
        }
    }
    public static function recuperarDatosDelete($data){

        $tabla=$data["tabla"];
        $ids=$data["where"];
        $pks=$data["pk"];
        $columnas=$data["columna"];

        $i=0;
        $pk="";

        foreach($pks as $c){
            if($i==0){

                $pk="to_char(".$c.")";
            }else{

                $pk.="||to_char(".$c.")";
            }
            $i++;
        }
        $col="";
        $i=0;
        foreach($columnas as $c){
            if($i==0){
                $col="to_char(".$c.") as ".$c;
            }else{
                $col.=",to_char(".$c.") as ".$c;
            }
            $i++;
        }


        $where="";
        $i=0;
        foreach($ids as $key=>$value){
            if($i==0){
                $where=" ".$key."='".$value."'";
            }else{
                $where.=" and ".$key."='".$value."'";
            }
            $i++;
        }
        $query = "SELECT ".$pk." as pktabla, ".$col."
                FROM ".$tabla."
                WHERE ".$where;
        $oQuery = (object)DB::select($query);

        return $oQuery;
    }
    public static function verificaTipoCambio(){
        $query = "SELECT
                        NVL(COS_VENTA,0) VENTA,
                        NVL(COS_COMPRA,0)COMPRA,
                        NVL(COS_DENOMINACIONAL,0) DENOMINACIONAL
                FROM TIPO_CAMBIO
                WHERE ID_MONEDA = 9
                AND to_char(FECHA,'DDMMYYYY') = to_char(SYSDATE,'DDMMYYYY') ";
        $oQuery = DB::select($query);
        $tc=false;
        $venta=0;
        $compra=0;
        $denominacional=0;
        if(count($oQuery)>0){
            foreach ($oQuery as $item){
                $venta = $item->venta;
                $compra = $item->compra;
                $denominacional = $item->denominacional;
                if($venta>0 and $compra >0 and $denominacional>0){
                    $tc=true;

                }
            }
        }
        $retur = [
            'tc' => $tc,
            'venta' => $venta,
            'compra'=>$compra,
            'denominacional'=>$denominacional
        ];

        return $retur;
    }
    public static function setSecret($string,$key){
        /*if (in_array($key, $this->encryptable)) {
            if ($value) {
                $value = Crypt::encrypt($value);
            }
        }*/
        //return parent::setSecret($key, $value);
        //return Crypt::encrypt($value);
        $result = '';
        for($i=0; $i<strlen($string); $i++) {
           $char = substr($string, $i, 1);
           $keychar = substr($key, ($i % strlen($key))-1, 1);
           $char = chr(ord($char)+ord($keychar));
           $result.=$char;
        }
        return base64_encode($result);
    }
    public static function getSecret($string,$key){
        /*if (in_array($key, $this->encryptable)) {
            if ($this->attributes[$key]) {
                return Crypt::decrypt($this->attributes[$key]);
            }
        }
        return parent::getSecret($key);*/
        //return Crypt::decrypt($value);
        $result = '';
        $string = base64_decode($string);
        for($i=0; $i<strlen($string); $i++) {
           $char = substr($string, $i, 1);
           $keychar = substr($key, ($i % strlen($key))-1, 1);
           $char = chr(ord($char)-ord($keychar));
           $result.=$char;
        }
        return $result;
    }
    public static function authorizationLambAPP($request){
        $token = $request->header('Authorization');
        $id_user="∫";
        if ($token) {
            session_id($token);
            session_start();
            $bindings = [
                'p_token' => $token
            ];
            $result = DB::executeProcedureWithCursor('spc_user_session_valida_app', $bindings);
            $val = $result[0];
            $valida=$val->active;
            $id_user = GlobalMethods::personaIDAPP($token);
        }else{
            $valida = "NO";
        }
        $code = "401";
        $jResponse = [
            'success' => false,
            'message' => 'Recurso no Autorizado',
            'data' => [],
            'valida'=>$valida,
            'code'=>$code,
            'id_user'=>$id_user,
            'token'=>$token
        ];

        return $jResponse;
    }
}