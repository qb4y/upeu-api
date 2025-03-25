<?php
namespace App\Http\Controllers\cw;
use Exception;
use App\Http\Controllers\Controller;
use Request;

class securityToken extends Controller{
    private $request;
    public static function validaToken($api_key) {
        $valida = false;
        try {
            session_id($api_key);
            session_start();
            $token=$_SESSION['token'];
            $valida = true;            
        } catch (Exception $e) {            
            $valida = false;
        }
        return $valida;
    }
}