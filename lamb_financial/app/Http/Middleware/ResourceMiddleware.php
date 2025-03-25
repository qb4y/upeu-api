<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use App\Http\Data\GlobalMethods;
class ResourceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,$ruta){
        $token  = $request->header('Authorization');
        //$token_decryp = GlobalMethods::getSecret($token,"L@MB");  
        $id_system = 0;
        $id_system = substr($token,-1);
        //$dato = explode("|", $token_decryp);
        $token_externo = "";
        if(!is_numeric($id_system)){
            $id_system = 0;
        }
        $id_system = 4;
        $method = $request->method();
        $query = "SELECT 
                    A.ID_METODO,
                    B.ID_RESOURCE,
                    C.ID_MODULO 
                FROM LAMB_METODO A, 
                LAMB_RESOURCE B,
                LAMB_RESOURCE_ACCION C
                WHERE A.ID_METODO=C.ID_METODO
                AND B.ID_RESOURCE=C.ID_RESOURCE 
                AND A.NOMBRE='".$method."'
                AND B.RUTA='".$ruta."'
                AND C.ID_MODULO=".$id_system." ";
        $rs = DB::select($query); 
        if($id_system != 2){
            $query = "SELECT TOKEN FROM LAMB_MODULO
                WHERE ID_MODULO = ".$id_system." ";
            $oQuery = DB::select($query);
            foreach ($oQuery as $item){
                $token_externo = $item->token;
            }
            
            if(count($rs)==0){
                $jResponse['success'] = false;
                $jResponse['message'] = "No tiene acceso a la ruta ".$ruta." metodo ".$method;
                $jResponse['data'] = [];
                $code = "401";
                return response()->json($jResponse,$code);
            }/*else{
                if($token_externo != $token){
                    $jResponse['success'] = false;
                    $jResponse['message'] = "Token Incorrecto";
                    $jResponse['data'] = [];
                    $code = "401";
                    return response()->json($jResponse,$code);
                }
            }*/
            
        }else{ 
            if(count($rs)==0){
                $jResponse['success'] = false;
                $jResponse['message'] = "No tiene acceso a la ruta ".$ruta." metodo ".$method;
                $jResponse['data'] = [];
                $code = "401";
                return response()->json($jResponse,$code);
            }
        }
        
        return $next($request);
    }
}
